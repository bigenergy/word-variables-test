<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\Exception\CopyFileException;
use PhpOffice\PhpWord\Exception\CreateTemporaryFileException;
use PhpOffice\PhpWord\Exception\Exception;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Settings;
use PhpOffice\PhpWord\TemplateProcessor;

class FileController extends Controller
{

    public function __construct()
    {
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws CopyFileException
     * @throws CreateTemporaryFileException
     */
    public function uploadFile(Request $request): JsonResponse
    {
        $uploadedDocument = new TemplateProcessor($request->file);

        if (count($uploadedDocument->getVariables())) {
            return response()->json($uploadedDocument->getVariables());
        }

        return response()->json(null, 404);

    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws CopyFileException
     * @throws CreateTemporaryFileException
     * @throws Exception
     */
    public function convertFile(Request $request): JsonResponse
    {
        $attributes = $request->all();
        $uploadedDocument = new TemplateProcessor($request->file);

        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('freeserif');

        // set variables from inputs

        foreach ($attributes as $key => $value) {
            if ($key != 'file') {
                $uploadedDocument->setValue($key, $value);
            }
        }

        $file = str_replace("/","-", "Файл-".rand()).".docx";
        $finalPath = Storage::disk('local')->path('/').$file;
        $uploadedDocument->saveAs($finalPath);

        // converting to pdf

        $domPdfPath = base_path('vendor/mpdf/mpdf');

        Settings::setPdfRendererPath($domPdfPath);
        Settings::setPdfRendererName('MPDF');

        $savedDocument = IOFactory::load($finalPath);
        $PDFWriter = IOFactory::createWriter($savedDocument,'PDF');
        $pdfFilename = "Файл-PDF-".rand().".pdf";
        $pdfPath = Storage::disk('public')->path('/').$pdfFilename;
        $PDFWriter->save($pdfPath);

        return response()->json(["url" => Storage::disk('public')->url($pdfFilename)]);
    }

}
