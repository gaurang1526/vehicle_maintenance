<?php







namespace App;







use Illuminate\Database\Eloquent\Model;







use Laravel\Passport\HasApiTokens;



use Illuminate\Notifications\Notifiable;



use Illuminate\Foundation\Auth\User as Authenticatable;



use Carbon\Carbon;

use Intervention\Image\Facades\Image;

use Illuminate\Support\Facades\Storage;

use Illuminate\Support\Facades\File;







use TCG\Voyager\Traits\Translatable;







class Repairer extends Authenticatable



{



    use HasApiTokens, Translatable, Notifiable;







    protected $translatable = ['first_name','last_name'];







    public function findForPassport($email) {



      return $this->whereEmail($email)->first();



    }



    



    public function getCustomeTranslation($model,$language){



      return  $model->first_name = $model->translate($language)->first_name;



    }







    



    public function getRepairerByPhoneNumber($phoneNumber){



      $data = Repairer::where('phone_number', $phoneNumber)->first();



      return $data;



    }







    public static function checkRepairerOtp($phoneNumber,$otp){



      return Repairer::where("phone_number",$phoneNumber)->where("verification_code",$otp)->first();



    }







    public function saveRepairer($repairer,$request){



        $repairerSaved = 0;



        $repairer->garageName = $request->garageName;



        $repairer->speciality = $request->speciality;



        $repairer->about = $request->about;



        $repairer->is_topdealer = $request->isTopDealer;



        $repairer->is_pickup = $request->isPickup;



        $repairer->is_authorized = $request->isAuthorize;



        $repairer->is_active = $request->isActive;



        $repairer->is_repairer = 1;

        $nameArray = explode(' ',$request->name);
        $repairer->first_name = $nameArray[0];

        $repairer->last_name = $nameArray[1];
        $repairer->email = $request->email;

        $repairer->phone_number = $request->phoneNumber;



        $repairerSaved = $repairer->save();



        return $repairerSaved;



    }







    public function saveRepairerImage($repairer, $request){

      $repairerSave = 0;

      //Image Upload starts
          $destination_path = base_path('public\storage\repairers');
          $now = Carbon::now()->format("Hms");
          $url = "repairers\\".$now;

          if($request->hasFile('repairerImage')) {
            $repairerImage = $request->file('repairerImage');
            $extension = $repairerImage->getClientOriginalExtension();
            Storage::disk('public')->put('/repairers/'.$repairerImage->getFilename().'.'.$extension,  File::get($repairerImage));
            $repairerImage =  'repairers\\'.$repairerImage->getFilename().'.'.$extension;
           
            $repairer->photo = $repairerImage;
          }

            



          if($request->hasFile('document')) {

            $document = $request->file('document');

            $extension = $document->getClientOriginalExtension();

            Storage::disk('public')->put('/repairers/'.$document->getFilename().'.'.$extension,  File::get($document));

             $document =  'repairers\\'.$document->getFilename().'.'.$extension;
             $repairer->document_path = $document;
          }



          if(!empty($request->file('garagePhoto'))){

            $garagePhotoArray =[];

            foreach ($request->file('garagePhoto') as $key => $value) {

              $garagePhoto = $value;

              $extension = $garagePhoto->getClientOriginalExtension();

              Storage::disk('public')->put('/repairers/'.$garagePhoto->getFilename().'.'.$extension,  File::get($garagePhoto));

              $garagePhoto =  'repairers\\'.$garagePhoto->getFilename().'.'.$extension;

               

              array_push($garagePhotoArray, $garagePhoto);

            }

            $garagePhotoArray = json_encode($garagePhotoArray);
            $repairer->garage_photo = $garagePhotoArray;
          }

      //Image Upload Ends
      //Repairer Save Starts
        $repairerSave = $repairer->save();
      //Repairer Save Ends
      return $repairerSave;

    }



}



