<?php

namespace App\Http\Controllers\Documents;

use App\Models\User;
use App\Traits\ApiTrait;
use Illuminate\Http\Request;
use App\Models\UserDocsImage;
use App\Models\UserDocumentation;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\Documents\UpdateDocs;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\Documents\UserDocumnetationRequest;
use App\Traits\Model;

class UserDocumentationController extends Controller
{
    use ApiTrait,Model;

    public function index()
    {
        $docs = UserDocumentation::with(['userDocsImages', 'doctor', 'user'])->where('user_id', Auth::id())->get();
        return $this->data(compact('docs'));
    }

    public function store(UserDocumnetationRequest $request)
    {
        $user = Auth::user();
        $user_doc = UserDocumentation::create([
            'desc'      => $request->desc,
            'type'      => $request->type,
            'user_id'   => $user->id,
        ]);
        // CHECK PHOTO
        if($request->hasFile('image')){
            $this->storeImages($request, $user_doc);
        }
        return $this->successMessage('Created Successfully');
    }

    public function show($id){
        $doc = UserDocumentation::with('userDocsImages')->where('id', $id)->first();
        return $this->data(compact('$doc'));
    }

    public function update(UpdateDocs $request, $id)
    {

        $user = Auth::user();
        $doc = UserDocumentation::with('userDocsImages')->where('id', $id)->first();
        if ($doc) {
            $doc->update([
                'desc'    => $request->desc,
                'type'    => $request->type,
                'user_id' => $user->id,
            ]);
            // CHECK PHOTO
            if($request->hasFile('image')){
                // $this->deleteDocsImages($doc);
                $this->storeImages($request, $doc);
                return $this->successMessage('Updated Successfully');
            }
            return $this->errorsMessage(['error' => 'Document not found']);
        }
    }

    public function delete($id)
    {
        $doc = UserDocumentation::with('userDocsImages')->where('id', $id)->first();
        if ($doc) {
            $this->deleteDocsImages($doc);
            $doc->delete();
            return $this->successMessage('Deleted Successfully');
        }
        return $this->errorsMessage(['error' => 'Document not found']);
    }
   
    public function deleteImage($id) {
        $doc = UserDocsImage::where('id', $id)->delete();
        return $doc;
        return $this->successMessage('Deleted Successfully');
    }
}
