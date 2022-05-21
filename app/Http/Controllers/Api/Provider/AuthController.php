<?php

namespace App\Http\Controllers\Api\Provider;

use App\Http\Controllers\Controller;
use App\Models\OrderDetails;
use App\Models\Provider;
use App\Models\CommercialRecords;
use App\Models\ProviderCategories;
use App\Models\Token;
use App\Models\User;
use App\Traits\GeneralTrait;
use App\Traits\PhotoTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    use GeneralTrait,PhotoTrait;
    public function login(request $request){
        $validator = Validator::make($request->all(), [
            'type'       => 'required|in:email,phone',
            'phone_code' => 'required_without:email',
            'phone'      => 'required_without:email',
            'email'      => 'required_without_all:phone,phone_code',
            'password'   => 'required',
        ]);
        if ($validator->fails()) {
            $code = $this->returnCodeAccordingToInput($validator);
            return $this->returnValidationError($code, $validator);
        }

        if($request->type == 'email'){
            if (provider()->attempt($request->only('email','password')))
            {
                $data = Provider::with('commercial_records','categories.category','nationality','town')->find(\provider()->user()->id);
                $data['provider_code'] = $data['id'] * 1515;
                return $this->returnData('data', $data);
            }
        }
        else{


            if (provider()->attempt($request->only('password','phone','phone_code')))
            {

                $data = Provider::with('commercial_records','categories.category','nationality','town')->find(\provider()->user()->id);
                $data['provider_code'] = $data['id'] * 1515;
                return $this->returnData('data', $data);
            }
        }
        return $this->returnError('E002','Wrong Data',403);
    }//end fun


    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone'             => 'required|unique:providers,phone',
            'phone_code'        => 'required',
            'password'          => 'required',
            'fake_name'         => 'nullable',
            'vat_number'        => 'required',
            'latitude'          => 'required|numeric',
            'longitude'         => 'required|numeric',
            'nationality_id'    => 'required',
            'town_id'           => 'required',
        ]);
        if ($validator->fails()) {
            $code = $this->returnCodeAccordingToInput($validator);
            return $this->returnValidationError($code, $validator,406);
        }

        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:providers,email',
        ]);
        if ($validator->fails()) {
            $code = $this->returnCodeAccordingToInput($validator);
            return $this->returnValidationError($code, $validator,407);
        }
        $validator = Validator::make($request->all(), [
            'vat_number' => 'nullable|unique:providers,vat_number',
        ]);
        if ($validator->fails()) {
            $code = $this->returnCodeAccordingToInput($validator);
            return $this->returnValidationError($code, $validator,408);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'image' => 'nullable|image|mimes:jpeg,jpg,png,gif',
        ]);
        if ($validator->fails()) {
            $code = $this->returnCodeAccordingToInput($validator);
            return $this->returnValidationError($code, $validator,400);
        }

        $validator = Validator::make($request->all(),[
            'nationality_id'    => 'exists:nationalities,id',
            'town_id'           => [Rule::exists('towns','id')
                ->where('nationality_id',$request->nationality_id)],
        ]);

        if ($validator->fails()) {
            return $this->returnError('E01','يرجي ادخال بيانات صحيحة لكل من البلد والمدينة',407);
        }

        $data = $request->except('category_ids','commercial_records_images');

        if($request->has('image')){
            $file_name = $this->saveImage($request->image,'assets/uploads/providers');
            $data['image'] = 'assets/uploads/providers/'.$file_name;
        }
        $data['password'] = Hash::make($request->password);

        $validator = Validator::make($request->all(), [
            'category_ids' => ["required","array",Rule::exists('categories','id')
                ->where('level',1)],
        ]);
        if ($validator->fails()) {
            $code = $this->returnCodeAccordingToInput($validator);
            return $this->returnValidationError($code, $validator,400);
        }
        $validator = Validator::make($request->all(), [
            'commercial_records_images' => 'required|array',
        ]);
        if ($validator->fails()) {
            $code = $this->returnCodeAccordingToInput($validator);
            return $this->returnValidationError($code, $validator,410);
        }

        $provider = Provider::create($data);



        foreach ($request->category_ids as $cat_id) {
            $assignCategories['provider_id'] = $provider->id;
            $assignCategories['category_id'] = $cat_id;
            ProviderCategories::create($assignCategories);
        }

        foreach ($request->commercial_records_images as $record) {
            $file_name = $this->saveImage($record,'assets/uploads/commercial');
            $assignCommercial['image'] = 'assets/uploads/commercial/'.$file_name;
            $assignCommercial['provider_id'] = $provider->id;
            CommercialRecords::create($assignCommercial);
        }
        $provider = Provider::with('commercial_records','categories.category','nationality','town')->find($provider->id);
        $provider['provider_code'] = $provider->id * 1515;
        return $this->returnData('data', $provider);
    }//end fun

    public function update_profile(request $request){
        $validator = Validator::make($request->all(), [
            'provider_id'=> 'required|exists:providers,id',
            'phone'      => 'required|unique:providers,phone,'.$request->provider_id,
            'phone_code' => 'required',
            'password'   => 'nullable',
        ]);
        if ($validator->fails()) {
            $code = $this->returnCodeAccordingToInput($validator);
            return $this->returnValidationError($code, $validator,406);
        }

        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:providers,email,'.$request->provider_id,
        ]);
        if ($validator->fails()) {
            $code = $this->returnCodeAccordingToInput($validator);
            return $this->returnValidationError($code, $validator,407);
        }

        $validator = Validator::make($request->all(), [
            'image' => 'nullable|image|mimes:jpeg,jpg,png,gif',
        ]);
        if ($validator->fails()) {
            $code = $this->returnCodeAccordingToInput($validator);
            return $this->returnValidationError($code, $validator,400);
        }


        $provider = Provider::with('commercial_records','categories.category','nationality','town')->find($request->provider_id);


        $data = $request->except('provider_id');

        if($request->hasFile('image')){
            $file_name = $this->saveImage($request->image,'assets/uploads/providers');
            $data['image'] = 'assets/uploads/providers/'.$file_name;
        }

        if($request->has('password')
            && $request->password != null){
            $data['password'] = Hash::make($request->password);
        }else{
            unset($data['password']);
        }

        $provider->update($data);
        $provider['provider_code'] = $provider->id * 1515;

        return $this->returnData('data', $provider);
    }//end fun
    /**
     * @param Request $request
     * @return mixed
     */
    public function logout(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'nullable|exists:users,id',
            'provider_id' => 'required_if:user_id,=,null|exists:providers,id',
            'token' => 'required',
        ]);
        if ($validator->fails()) {
            $code = $this->returnCodeAccordingToInput($validator);
            return $this->returnValidationError($code, $validator,406);
        }

        Token::where('token',$request->token)->delete();
        return $this->returnData('data',null,'done');
    }//end fun

}
