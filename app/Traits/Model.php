<?php

namespace App\Traits;

use App\Models\UserDocsImage;

trait Model
{
    public function uploadPhoto($image, $folderPath)
    {
        $photoName = uniqid() . '.' . $image->extension();
        $image->move(public_path("images/$folderPath"), $photoName);
        return $photoName;
    }

    public function deletePhoto($photoPath)
    {
        if (file_exists($photoPath)) {
            unlink($photoPath);
        }
    }

    public function storeImages($request, $table)
    {
        foreach ($request->image as $image) {
            $photoName =  $this->uploadPhoto($image, 'docs_images');
            $docs_image = new UserDocsImage();
            $docs_image->image = $photoName;
            $docs_image->user_documentations_id = $table->id;
            $docs_image->save();
        }
    }

    public function deleteDocsImages($doc)
    {
        foreach ($doc->userDocsImages as $table) {
            $oldPhoto = $table->image;
            if (!is_null($oldPhoto)) {
                $oldPath = public_path('images/docs_images/') . $oldPhoto;
                $this->deletePhoto($oldPath);
                $table->delete();
            }
        }
    }

    public function deletePhotoWithoutDefault($photoPath, $photoName)
    {
        if (file_exists($photoPath) && $photoName != 'default.jpg') {
            unlink($photoPath);
        }
    }

}