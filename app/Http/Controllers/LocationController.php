<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Entities\Location;
use App\Transformers\LocationTransformer;

class LocationController extends Controller
{
    /**
     * Show all location data.
     *
     * @return List of location data
     */
    public function index(Request $req)
    {
        try {
            //code...
            $limit = empty($req->input('limit')) ? 5 : $req->input('limit');
            $location = Location::orderBy('created_at', 'desc')->paginate($limit);
            $location = $this->paginate($location, new LocationTransformer());
            return $this->responseJSON('List of data found', $location);
        } catch (\Exception $ex) {
            //throw $ex;
            return $this->otherError($ex->getMessage(), $ex->getCode());
        }
    }

    /**
     * Show a single location data.
     *
     * @return Single location data
     */
    public function show($id)
    {
        try {
            //code...
            if($location = Location::find($id)) return $this->notFound('Location', 404, $id);
            $location = $this->item($location, new LocationTransformer());
            return $this->responseJSON('Location with id = '. $id . ' foung', $location);
        } catch (\Exception $ex) {
            //throw $th;
            return $this->otherError($ex->getMessage(), $ex->getCode());
        }
    }

    /**
     * Adding a new location
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
                'name' => 'required|max:255|unique:locations',
                'last_updated_by' => 'required|exists:users,id'
            ]);
            if ($validator->fails()) {
                return $this->validationError($validator->errors());
            }
            $new_location = Location::create([
                'name' => $request->input('name'),
                'last_updated_by' => $request->input('last_updated_by')
            ]);
            $new_location = $this->item($new_location, new LocationTransformer());
            DB::commit();
            return $this->responseJSON('Data is stored successfully!', $new_location);
        } catch (\Exception $ex) {
            //throw $ex;
            DB::rollback();
            return $this->otherError($ex->getMessage(), $ex->getCode());
        }
    }

    /**
     * Update a single data
     */
    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            //code...
            $validator = Validator::make($request->all(), [
                'name' => 'unique:locations|max:255',
                'last_updated_by' => 'required|exists:users,id'
            ]);
            if ($validator->fails()) {
                return $this->validationError($validator->errors());
            }
            if($location = Location::find($id)) return $this->notFound('Location', 404, $id);
            $location->update([
                'name' => $request->input('name') ? $request->input('name'):$location->name,
                'last_updated_by' => $request->input('last_updated_by') ? $request->input('last_updated_by'):$location->last_updated_by
            ]);
            $location = $this->item($location, new LocationTransformer());
            DB::commit();
            return $this->responseJSON('Location with id = '. $id . 'is updated', $location);
        } catch (\Exception $ex) {
            //throw $ex;
            DB::rollback();
            return $this->otherError($ex->getMessage(), $ex->getCode());
        }
    }

    /**
     *
     * Delete location by location id
     *
     * @require location id
     *
     * @return message delete success
     */

     public function destroy($id)
     {
         DB::beginTransaction();
        try {
            //code...
            if(!$location=Location::find($id)) return $this->notFound('Location', 404, $id);
            $location->delete();
            DB::commit();
            return $this->responseJSON('Delete success', ['id'=> $id]);
        } catch (\Exception $ex) {
            //throw $ex;
            DB::rollback();
            return $this->otherError($ex->getMessage(), $ex->getCode());
        }
     }
}
