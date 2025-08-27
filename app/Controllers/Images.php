<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\ImageModel;

class Images extends ResourceController
{
    protected $modelName = 'App\\Models\\ImageModel';
    protected $format    = 'json';

    public function index()
    {
        $model = new ImageModel();
        $data = $model->findAll();
        return $this->respond($data);
    }

    public function show($id = null)
    {
        $model = new ImageModel();
        $data = $model->find($id);
        if ($data) {
            $payload = is_array($data) ? $data : (array) $data;
            return $this->respond($payload);
        }
        return $this->failNotFound('Imagen no encontrada');
    }

    public function create()
    {
        helper('url');
        $file = $this->request->getFile('image');
        if (!$file || !$file->isValid()) {
            return $this->failValidationErrors('No se subió ninguna imagen válida.');
        }

        $newName = $file->getRandomName();
        $uploadPath = WRITEPATH . 'uploads/';
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }
        if (!$file->move($uploadPath, $newName)) {
            return $this->failServerError('Error al mover el archivo.');
        }

        $publicUrl = base_url('uploads/' . $newName);
        $enlace = $this->request->getVar('enlace');

        $model = new ImageModel();
        $data = [
            'filename' => $newName,
            'url' => $publicUrl,
            'enlace' => $enlace,
            'created_at' => date('Y-m-d H:i:s'),
        ];
        $model->insert($data);

        return $this->respondCreated($data);
    }

    public function delete($id = null)
    {
        $model = new ImageModel();
        $image = $model->find($id);
        if (!$image) {
            return $this->failNotFound('Imagen no encontrada');
        }

        $filename = is_array($image) ? ($image['filename'] ?? null) : ($image->filename ?? null);
        if ($filename) {
            $filePath = WRITEPATH . 'uploads/' . $filename;
            if (is_file($filePath)) {
                @unlink($filePath);
            }
        }

        $model->delete($id);
        return $this->respondDeleted(['id' => $id]);
    }
}
