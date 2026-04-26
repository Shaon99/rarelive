<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;

class DownloadManager extends Controller
{
    public function index()
    {
        $data['pageTitle'] = 'Download Manager';
        $data['downloadManagerActiveClass'] = 'active';
        $data['activities_active'] = 'active';

        // Get all files
        $files = Storage::files('public/');

        // Create a collection with metadata
        $fileData = collect($files)->map(function ($file) {
            return [
                'name' => basename($file),
                'url' => asset('storage/'.str_replace('public/', '', $file)),
                'date' => Carbon::createFromTimestamp(Storage::lastModified($file))->format('d M, Y h:i A'),
                'timestamp' => Storage::lastModified($file),
            ];
        })->sortByDesc('timestamp')->values(); // Sort by latest date and reset keys

        // Paginate manually
        $perPage = 15;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $currentPageItems = $fileData->slice(($currentPage - 1) * $perPage, $perPage)->values();

        $paginatedFiles = new LengthAwarePaginator(
            $currentPageItems,
            $fileData->count(),
            $perPage,
            $currentPage,
            ['path' => LengthAwarePaginator::resolveCurrentPath()]
        );

        $data['files'] = $paginatedFiles;

        return view('backend.download_manager.index', $data);
    }

    public function delete($file)
    {
        $fileName = $file;
        $filePath = storage_path('app/public/'.$fileName);

        if (file_exists($filePath)) {
            unlink($filePath);

        }

        return back()->with('success', 'File deleted successfully!!!');
    }

    public function deleteMultiple(Request $request)
    {
        $fileNames = $request->input('files', []);

        if (! empty($fileNames)) {
            foreach ($fileNames as $fileName) {
                $filePath = 'public/'.$fileName;
                if (Storage::exists($filePath)) {
                    Storage::delete($filePath);
                }
            }

            return redirect()->back()->with('success', 'Selected files have been deleted successfully.');
        }

        return redirect()->back()->with('error', 'No files selected for deletion.');
    }
}
