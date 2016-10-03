<?php
/**
 * Frosit magerun RewriteToolset
 *
 * @category    project
 * @package     magerun-RewriteToolset
 * @author      Fabio Ros <info@frosit.nl>
 * @copyright   Copyright (c) 2016 Fabio Ros - FROSIT
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace Frosit\Magento\Command\Rewrites\Fix;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * Class ProductsCommand
 * @package Frosit\Magento\Command\Rewrites\Fix
 */
class ProductsCommand extends AbstractFixCommand
{
    // dynamic values
    protected $_dryRun;
    protected $_seperator;
    protected $_newSuffix;

    /**
     * Configuration command
     */
    protected function configure()
    {
        $this
            ->setName('rewrites:fix:products')
            ->addOption('new-suffix', null, InputOption::VALUE_OPTIONAL, 'Specify a new suffx, current_url, product_id or sku', 'current_url')
            ->addOption('seperator', null, InputOption::VALUE_OPTIONAL, 'Seperator between original url and new suffix', "-")
            ->addOption('limit', null, InputOption::VALUE_OPTIONAL, 'Limits the amount of products to load for key fixing.')
            ->setHelp("This command makes the url keys of duplicated products unique by specified new-suffix. Runs on the global scope and skips products with url_key configuration in lower scopes. this method is relatively slow.")
            ->setDescription('Make the url keys unique for duplicated products in a simple way by using Magento\'s methods [development].');
    }

    /**
     * RewriteClean constructor.
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectMagento($output);
        if (!$this->initMagento()) {
            return;
        } else {
            $this->_input = $input;
            $this->_output = $output;
            $this->setRewriteToolsCommandConfig($this->getCommandConfig());
        }

        if ($input->getOption('dry-run')) {
            $this->_dryRun = true;
        }

        $helper = $this->getHelper('question');
        if (!$input->getOption('dry-run')) {
            $question = new ConfirmationQuestion("<error>Caution!</error> This command changes URL keys, with the risk of losing SEO scores. Backups and test setups are adviced.<question>Continue?</question><comment> [Y/n]</comment>", false);
            if (!$helper->ask($input, $output, $question)) {
                return;
            }
        }

        $statistics = array();
        $this->_seperator = $input->getOption('seperator');
        $this->_newSuffix = $input->getOption('new-suffix');

        // ==== Getting attributes
        $entityType = $this->getEntityType("catalog_product");
        $attributesCollection = $entityType->getAttributeCollection()
            ->addFieldToFilter('attribute_code', array('in' => array('url_key', 'url_path')));
        $attributes = [];
        foreach ($attributesCollection->load() as $attribute) {
            $attributes[$attribute->getAttributeCode()] = $attribute->getData();
        }

        // === foreach here
        $store = $this->getStoreById(0); // admin store

        // === get duplicates
        $dupes = $this->getDupedProducts($store, $input->getOption('limit'));
        $this->_info("Found <comment>" . count($dupes) . " products with duplicates for store <comment>" . $store['store_id'] . "</comment></comment>");

        $suffix = $this->getSeoConfiguration("catalog/seo/product_url_suffix", $store);

        foreach ($dupes as $dupe) {
            $product = \Mage::getModel('catalog/product')->load($dupe['product_id']);
            $urlKey = $product->getUrlKey();
            $urlPath = $product->getUrlPath();
            $sku = $product->getSku();
            $newUrlKey = false;

            if ($this->detectStoreDifferentials($dupe['product_id'], $attributes['url_key']['attribute_id'])) {
                if ($this->_newSuffix == "current_url") {
                    $newUrlKey = str_replace($suffix, "", $urlPath);
                } elseif ($this->_newSuffix == "product_id") {
                    $newUrlKey = $urlKey . $this->_seperator . $dupe['product_id'];
                } elseif ($this->_newSuffix == "sku") {
                    $newSuffix = $this->cleanUrlChars(\Mage::helper('catalog/product_url')->format($sku));
                    $newUrlKey = $urlKey . $this->_seperator . $newSuffix;
                } else {
                    $this->_error("no new suffix specified");
                }
            } else {
                $this->_error("Product " . $dupe['product_id'] . " seems to have a setting a store view level which currently is not supported. Skipping...");
            }

            if ($newUrlKey) {
                if (!$this->_dryRun) {
                    $product->setData('url_key', $newUrlKey);
                    $product->save();
                }
                $this->_info("Set URL key for product " . $dupe['product_id'] . " from " . $urlKey . " to " . $newUrlKey);

                $statistics[] = array(
                    "product_id" => $dupe['product_id'],
                    "old_url_key" => $urlKey,
                    "new_url_key" => $newUrlKey,
                );
            }
        }

        $this->_info("Done fixing duplicated url keys for " . count($statistics) . " out of " . count($dupes) . "products with duplicated keys");

        $this->processCommandEnd($statistics);
    }

    /**
     * Gets duped products
     * @param $store
     * @param bool $limit
     * @return array|bool
     */
    public function getDupedProducts($store, $limit = false)
    {
        $storeId = $store['store_id'];
        if ($storeId == 0) {
            $storeId = false;
        }
        $db = $this->getDb();
        $query = "
        SELECT `product_id`, COUNT(*)  AS magnitude 
        FROM `core_url_rewrite`
        WHERE `is_system` = '0'
        AND `product_id` REGEXP '^[0-9]+$'";
        if ($storeId) {
            $query .= "AND `store_id` = '" . $storeId . "'";
        }
        $query .= " 
         GROUP BY `product_id` 
         ORDER BY magnitude DESC ";

        if ($limit) {
            $query .= " LIMIT " . $limit;
        }
        $dupes = $db->rawQuery($query);
        if ($dupes) {
            return $dupes;
        } else {
            $this->_error($db->getLastError());
            return false;
        }
    }

    /**
     * Detects potential store view override
     * @param $productId
     * @param $attributeId
     * @return bool
     */
    public function detectStoreDifferentials($productId, $attributeId)
    {
        $db = $this->getDb();
        $db->where('entity_id', $productId);
        $db->where('attribute_id', $attributeId);
        $entities = $db->get('catalog_product_entity_varchar', null, 'store_id,value');
        if ($entities) {

            $values = array();
            foreach ($entities as $entity) {
                $values[] = $entity['value'];
            }
            $values = array_unique($values);


            if (count($values) > 1) {
                return false;
            } else {
                return true;
            }
        } else {
            $this->_error($db->getLastError());
            return false;
        }
    }

}
