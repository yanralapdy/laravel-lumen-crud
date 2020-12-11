<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Entities\Education;
use App\Transformers\EducationTransformer;

class EducationController extends Controller
{
    /**
     * Show all education data.
     *
     * @return List of education data
     */
    public function index(Request $req)
    {
        try {
            //code...
            $limit = empty($req->input('limit')) ? 5 : $req->input('limit');
            $response = $this->paginate(Education::orderBy('updated_at', 'desc')->paginate($limit), new EducationTransformer());
            return $this->responseJSON('List of data found', $response);
        } catch (\Exception $ex) {
            //throw $th;
            return $this->otherError($ex->getMessage(), $ex->getCode());
        }
    }

    /**
     * Show a single education data.
     *
     * @return Single education data
     */
    public function show($id)
    {
        try {
            //code...
            if(!$education = Education::find($id)) return $this->notFound('Education', 404, $id);
            $response=$this->item($education, new EducationTransformer());
            return $this->responseJSON('Education with id = '. $id . ' found', $response);
        } catch (\Exception $ex) {
            //throw $th;
            return $this->otherError($ex->getMessage(), $ex->getCode());
        }
    }

    /**
     * Adding a new education
     *
     * @request name, last_updated_by, email, password, role
     *
     * @return message success
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            //code...
            $validator = Validator::make($request->all(), [
                'name' => 'unique:educations|required|max:255',
                'last_updated_by' => 'required|exists:users,id'
            ]);
            if ($validator->fails()) {
                return $this->validationError($validator->errors());
            }
            $new_education = Education::create([
                'name' => $request->input('name'),
                'last_updated_by' => $request->input('last_updated_by')
            ]);
            $new_education= $this->item($new_education, new EducationTransformer());
            DB::commit();
            return $this->responseJSON('Data is stored successfully !', $new_education, 201);
        } catch (\Exception $ex) {
            //throw $th;
            DB::rollback();
            return $this->otherError($ex->getMessage(), $ex->getCode());
        }
    }
    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            //code...
            $validator = Validator::make($request->all(), [
                'name' => 'unique:educations|max:255',
                'last_updated_by' => 'required|exists:users,id'
            ]);
            if ($validator->fails()) {
                return $this->validationError($validator->errors());
            }
            if($education = Education::find($id)) return $this->notFound('Education', 404, $id);
            $education->update([
                'name' => $request->input('name') ? $request->input('name'):$education->name,
                'last_updated_by' => $request->input('last_updated_by') ? $request->input('last_updated_by'):$education->last_updated_by
            ]);
            $education = $this->item($education, new EducationTransformer());
            DB::commit();
            return $this->responseJSON('Education with id = '. $id .'updated successfully', $education);
        } catch (\Exception $ex) {
            //throw $th;
            DB::rollback();
            return $this->otherError($ex->getMessage(), $ex->getCode());
        }
    }

    /**
     *
     * Delete education by education id
     *
     * @require education id
     *
     * @return message delete success
     */

     public function destroy($id)
     {
         DB::beginTransaction();
        try {
            //code...
            if(!$education=Education::find($id)) return $this->notFound('Education', 404, $id);
            $education->delete();
            DB::commit();
            return $this->responseJSON('Delete success', ['id'=> $id]);
        } catch (\Exception $ex) {
            //throw $th;
            DB::rollback();
            return $this->otherError($ex->getMessage(), $ex->getCode());
        }
     }
}
