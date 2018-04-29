<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 29/04/2018
 * Time: 16:12
 */
namespace App\Command;

use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;

class ImportOrdersCommand extends Command
{
    private $em;

    public function __construct(?string $name = null, EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this
            ->setName('app:import-orders')
            ->setDescription('Import orders from XML input')
            ->setHelp('This command allows you to import XML orders')
            ->addArgument('inputFile', InputArgument::REQUIRED, 'Path to the file')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln([
            'XML Orders importer',
            '===================',
            ''
        ]);

        $pathToFile = $input->getArgument('inputFile');

        $json  = json_encode(simplexml_load_string(file_get_contents($pathToFile), null, LIBXML_NOCDATA));
        $configData = json_decode($json, true);

        $cpt = 0;
        foreach ($configData['orders'] as $xmlOrders) {
            foreach ($xmlOrders as $xmlOrder) {
                $order = new Order();
                $order
                    ->setMarketPlace($xmlOrder['marketplace'])
                    ->setAmount($xmlOrder['order_amount'])
                    ->setCurrency($xmlOrder['order_currency'])
                ;

                if (!\is_array($xmlOrder['order_purchase_date'])) {
                    $order->setPurchaseDate(\DateTime::createFromFormat('Y-m-d', $xmlOrder['order_purchase_date']));
                }
                $this->em->persist($order);
                $cpt++;
            }
        }
        $this->em->flush();
        $output->writeln(sprintf("%d orders imported", $cpt));
    }
}
