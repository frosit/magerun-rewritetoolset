<?php
/**
 * Frosit magerun RewriteToolset
 *
 * @category    project
 * @package     magerun-RewriteToolset
 * @author      Fabio Ros <info@frosit.nl>
 * @copyright   Copyright (c) 2016 Fabio Ros - FROSIT
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 *
 * @todo better cleaning by simple / invisible / out of stock products
 * @todo add functionality for detecting and or leaving the last two
 * @todo write another command for keeping worty google urls by analytics CSV
 * @add possibility for removing also non-disabled ones, a more aggresive approach
 *
 * @note - this is for disabled entities and considered to be a POC boilerplate for other methods
 */

namespace Frosit\Magento\Command\Rewrites\Clean;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * Class CleanCommand
 * @package Frosit\Magento\Command\Dev\Rewrites
 */
class DisabledCommand extends AbstractCleanCommand
{
    // dynamic values
    protected $_limit = false;
    protected $_dryRun;

    // placeholder values
    protected $_storeViews; //store_id
    protected $_statusAttributeId; // attribute_id for the product status attribute


    /**
     * Configuration command
     */
    protected function configure()
    {
        $this
            ->setName('rewrites:clean:disabled')
            ->addOption('limit', null, InputOption::VALUE_OPTIONAL, 'Specify a limit for testing purposes')
            ->setDescription('Cleans out some redundant rewrites based on disabled products or store views. [experimental]');
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

        if ($input->getOption('limit')) {
            $this->_limit = $input->getOption('limit');
        }


        //temp
//        ini_set('display_errors', 1);
//        ini_set('memory_limit', '4000M');

        // fetching variables @todo maybe redundant too - add to abstract
        $storageDir = $this->_storageDir = \Mage::getBaseDir('var') . DS . 'rewritecleaner' . DS;
        $entityTypeId = $this->_entityTypeId = \Mage::getModel('eav/entity')->setType('catalog_product')->getTypeId();
        $statusAttributeId = $this->_statusAttributeId = $this->getActiveStatusEntityId();

        $stores = $this->prepareStoresFromInput($input->getOption('store'));

        $statistics = array();

        // header
        $this->writeSection($output, 'Rewrites Cleaning. [FROSIT]');


        $confirmHelper = $this->getHelper('question');
        $confirmation = new ConfirmationQuestion("<error>This command is experimental, continue at your own risk.</error>", false);

        if (!$confirmHelper->ask($input, $output, $confirmation)) {
            return;
        }

        // create tasks
        $tasks = $this->prepareCleaningTasks($stores);

        // ======== Process Cleaning Tasks ====
        $taskResults = $this->processCleaningTasks($tasks);

        $statistics = $taskResults;

        $output->writeln('<info>Finished executing <info>' . count($taskResults) . '</info> task(s)</info>');

        $this->processCommandEnd($statistics);


    }

    /**
     * Processes specified cleaning tasks
     * @param $tasks
     * @return array|bool
     */
    public function processCleaningTasks($tasks)
    {
        if (is_array($tasks)) {

            $results = array();
            $this->_output->writeln('<info>Found <comment>' . count($tasks) . '</comment> tasks to process.</info>');

            foreach ($tasks as $task) {

                /**
                 * At this point we have some options depending on the safety and speed
                 * 1. Bulk all ID's in OR statements and send a blast of SQL query's - recommended for +3Gb tables or stores in big trouble - we keep a log of product ID's for an afterwards action of fixing - must build it first though
                 * 2. loop all product ID's for fetching and removing. We can do stuff like keep the newest ones, verify bugs, use a Google file for whitelisting, immedeatly fix the products etc.
                 * 3. Just truncate the shit out of that table with WHERE statements only on the disabled stores
                 * 4. Only remove rewrites after x timestamp (we know which rewrites for created at which date)
                 *
                 * And a lot of strategies more
                 *
                 * We can dry run - just gather stats, only the select statements are kinda heavy but your bulking them
                 * We can do a clean process while checking mytop through a subshell too modify the impact of this command, have it rest sometime
                 * We can run Atwix style, which is nice but slow
                 *
                 * Must solve the issue structurally though...
                 *
                 * Let's nuke some rewrites for stores in big trouble
                 */

                // @todo can run different types of cleaning tasks

                $result = $this->runDisabledTask($task);
                if ($result) {
                    $results[] = $result;
                }
            }
            return $results;

        } else {
            $this->_output->writeln('<error>Something went wrong with the task data.</error>');
            return false;
        }
    }

    /**
     * Removes rewrites from disabled entities
     * @note use this when in trouble
     * @param $task
     * @return array|bool|mixed
     */
    public function runDisabledTask($task)
    {
        // Ask the user if he or she is okay with this
        $result = array();
        $dialog = $this->getHelper('dialog');
        $storeName = $task['store_data']['store_id'] . ':' . $task['store_data']['name'];
        $type = false;
        if ($task['active'] && count($task['product_ids'])) {
            $message = '<question>Found <comment>' . count($task['product_ids']) . '</comment> disabled products for store <comment>' . $storeName . '</comment> Are you sure to remove it? [Y/n]</question>';
            $type = 'delete_rewrites_per_store_by_product_id';
        } elseif (!$task['active']) {
            $message = '<question>Store <comment>' . $storeName . '</comment> is disabled, are you sure you want to wipe all it\'s rewrites? [Y/n]</question>';
            $type = 'delete_rewrites_per_store';
        } else {
            $message = '<info><error>Error:</error> Could not define task for <comment>' . $storeName . '</comment> continue with remainder tasks? [Y/n]</info>';
        }

        if (!$dialog->askConfirmation(
            $this->_output,
            $message,
            false
        )
        ) {
            return false;
        }


        // alright, user has given consent to wreck his database
        if ($type == "delete_rewrites_per_store_by_product_id") {
            $result[$type] = $this->deleteRewritesPerStoreByProductId($task['product_ids'], $task['store_data']['store_id']);
        } elseif ($type == "delete_rewrites_per_store") {
            $result[$type] = $this->deleteAllRewritesFromStore($task['store_data']['store_id']);
        }
        if ($result[$type]) {
            $result['task'] = $task;
        }
        return $result;
    }


    /**
     * Prepares tasks for cleaning
     * @todo can be splitted to create different tasks, maybe make this abstract
     * @param $stores
     * @return array|bool
     */
    public function prepareCleaningTasks($stores)
    {

        if ($stores) {
            $task = array();

            foreach ($stores as $store) {
                $storeId = $store['store_id'];
                $ids = array();

                // if active store, we find disabled products
                // @todo also do categories
                if ($this->isStoreActive($store)) {

                    // first get products globally disabled (view 0)
                    $globallyDisabledProductIds = $this->getDisabledProductsByStore(0, $this->_statusAttributeId);
                    // then we check if status is not overridden in target store view
                    foreach ($globallyDisabledProductIds as $productId) {
                        if ($this->isProductDisabledForStore($storeId, $productId)) {
                            $ids[] = $productId; // nope, add it
                        }
                    }

                    // add products disabled in target store view
                    $storeDisabledProducts = $this->getDisabledProductsByStore($storeId, $this->_statusAttributeId);
                    foreach ($storeDisabledProducts as $disabledProduct) {
                        $ids[] = $disabledProduct;
                    }

                    // add them if we found some
                    if (count($ids) > 0) {
                        // add task info
                        $task[$storeId] = array(
                            "active" => true,
                            "product_ids" => $ids,
                            "store_data" => $store
                        );
                    }

                } else {
                    // add task for mass wiping
                    $task[$storeId] = array(
                        "active" => false,
                        "product_ids" => false,
                        "store_data" => $store
                    );
                }
            }

            return $task;
        } else {
            return false;
        }
    }


    /**
     * Deletes rewrites
     * @todo maybe faster in bulk query
     * @param $ids
     * @param $storeId
     * @note reports should come from here
     * @return array
     */
    public function deleteRewritesPerStoreByProductId($ids, $storeId, $bulk = false)
    {

        $results = array();

        foreach ($ids as $id) {
            $query = '';
            if ($this->_dryRun) {
                $query .= "SELECT COUNT(*) ";
            } else {
                $query .= "DELETE ";
            }
            $query .= "FROM `core_url_rewrite` WHERE `store_id` = " . $storeId . " AND `product_id` = " . $id;


            $connection = $this->getConnection();

            if ($this->_dryRun) {
                $result = $connection->fetchOne($query);
            } else {
                $result = $connection->query($query)->rowCount();
            }

            $results[$id] = $result;

            $this->_output->writeln('<info>Removed <comment>' . $result . '</comment> rows for product with id: <comment>' . $id . '</comment> from the database.</info>');
        }
        return $results;
    }

    /**
     * Deletes all Rewrites from specified store
     * @param $storeId
     * @return mixed
     */
    public function deleteAllRewritesFromStore($storeId)
    {
        $query = '';
        if ($this->_dryRun) {
            $query .= "SELECT COUNT(*) ";
        } else {
            $query .= "DELETE ";
        }
        $query .= "FROM `core_url_rewrite` WHERE `store_id` = " . $storeId;
        $connection = $this->getConnection();

        if ($this->_dryRun) {
            $result = $connection->fetchOne($query);
        } else {
            $result = $connection->query($query)->rowCount();
        }

        $this->_output->writeln('<info>Removed <comment>' . $result . '</comment> rows for store <comment>' . $storeId . '</comment> from the database.</info>');
        return $result;
    }


    /**
     * Fetches the matching products for the store.
     * @todo research and add simple products with no store visibility, maybe also for long out of stock products
     * @param $storeId
     * @param $attributeId
     * @return array
     */
    public function getDisabledProductsByStore($storeId, $attributeId)
    {
        $resource = \Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $query = 'SELECT `entity_id` FROM `catalog_product_entity_int` WHERE `store_id` = ' . intval($storeId) . ' AND `attribute_id` = ' . intval($attributeId) . ' AND `value` = ' . $this->_disabledValue;

        if ($this->_limit) {
            $query .= ' LIMIT ' . intval($this->_limit) . ';';
        }

        $results = $readConnection->fetchAll($query);
        $ids = array_column($results, 'entity_id');
        return $ids;
    }


    /**
     * Gets the entity_id for the product is active field.
     * @return int
     */
    public
    function getActiveStatusEntityId()
    {
        $model = \Mage::getModel('catalog/resource_eav_attribute')
            ->getIdByCode("catalog_product", "status");
        return $model;
    }


}

