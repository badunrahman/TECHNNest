<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Domain\Models\ProductsModel;
use App\Domain\Models\ReportsModel;
use DI\Container;
use Dompdf\Dompdf;
use Dompdf\Options;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ReportsController extends BaseController
{
    private ReportsModel $reportsModel;
    private ProductsModel $productsModel;

    // loading the models needed for teh reports
    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->reportsModel = $container->get(ReportsModel::class);
        $this->productsModel = $container->get(ProductsModel::class);
    }

    // main dashboard page with all the stats
    public function index(Request $request, Response $response): Response
    {
        // getting the raw numbers from db
        $totalOrders = $this->reportsModel->getTotalOrders();
        $totalRevenue = $this->reportsModel->getTotalRevenue();
        $totalCustomers = $this->reportsModel->getTotalCustomers();

        // just guessing 50% profit since idk why
        $estimatedProfit = $totalRevenue * 0.50;

        $data = [
            'page_title' => 'Reports & Statistics',
            'stats' => [
                'total_orders' => $totalOrders,
                'total_revenue' => $totalRevenue,
                'total_customers' => $totalCustomers,
                'estimated_profit' => $estimatedProfit
            ]
        ];

        return $this->render($response, 'admin/reports/index.php', $data);
    }


    // logic to generate the pdf using dompdf
    public function exportProductsPdf(Request $request, Response $response): Response
    {
        $products = $this->productsModel->getAllProducts();

        /// setting up the data for the pdf view
        $viewData = [
            'title' => trans('admin.products_report.title') ?: 'Products Report',
            'date' => date('Y-m-d H:i:s'),
            'headers' => [
                'id' => trans('admin.products_report.id') ?: 'ID',
                'name' => trans('admin.products_report.name') ?: 'Name',
                'category' => trans('admin.products_report.category') ?: 'Category',
                'price' => trans('admin.products_report.price') ?: 'Price',
                'stock' => trans('admin.products_report.stock') ?: 'Stock',
            ],
            'products' => $products
        ];

        // rendesr HTML
        // using output buffering here to grab the html string instead of showing it
        ob_start();
        extract($viewData);
        include APP_VIEW_PATH . '/admin/reports/productsPdfView.php';
        $html = ob_get_clean();

        // generating PDF
        $options = new Options();
        $options->set('defaultFont', 'Helvetica');


        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $pdfOutput = $dompdf->output();

        // setting headers to force download instead of just opening
        $response = $response->withHeader('Content-Type', 'application/pdf')
                             ->withHeader('Content-Disposition', 'attachment; filename="products-report.pdf"')
                             ->withHeader('Content-Length', (string) strlen($pdfOutput));

        $response->getBody()->write($pdfOutput);

        return $response;
    }
}
