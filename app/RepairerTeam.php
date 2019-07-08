<?php



namespace App;



use Illuminate\Database\Eloquent\Model;

use TCG\Voyager\Traits\Translatable;

use Carbon\Carbon;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;



class RepairerTeam extends Model

{

    use Translatable;

    protected $translatable = ['name','license_no'];

    

    public function getRepairerTeam($userId){

    	$data = RepairerTeam::where("repairer_id",$userId)->get();

    	return $data;

    }



    public function addTeamMember($request,$userId){
        $repairerTeam = ($request->memberId == 0) ? new RepairerTeam() : RepairerTeam::find($request->memberId);
        if(empty($repairerTeam)) {
            return false;
        }else{
            //Image Upload starts
                $destination_path = base_path('public\storage\repairer-teams');
                $now = Carbon::now()->format("Hms");
                $url = "repairer-teams\\".$now;

                if($request->hasFile('memberImage')) {
                    $memberImage = $request->file('memberImage');
                    $extension = $memberImage->getClientOriginalExtension();
                    Storage::disk('public')->put('/repairer-teams/'.$memberImage->getFilename().'.'.$extension,  File::get($memberImage));
                    $memberImage =  'repairer-teams\\'.$memberImage->getFilename().'.'.$extension;
                }else{
                    $memberImage = "";
                }
                
                if($request->hasFile('memberLicenceImage')) {
                    $memberLicenceImage = $request->file('memberLicenceImage');
                    $extension = $memberLicenceImage->getClientOriginalExtension();
                    Storage::disk('public')->put('/repairer-teams/'.$memberLicenceImage->getFilename().'.'.$extension,  File::get($memberLicenceImage));
                    $memberLicenceImage =  'repairer-teams\\'.$memberLicenceImage->getFilename().'.'.$extension;
                }else{
                    $memberLicenceImage = "";
                }

                
            //Image Upload Ends

            $repairerTeam->name = $request->memberName;
            $repairerTeam->photo = $memberImage;
            $repairerTeam->mobile_number = $request->memberContact;
            $repairerTeam->license_no = $request->memberLicenceNumber;
            $repairerTeam->document = $memberLicenceImage;
            $repairerTeam->repairer_id = $userId;
            $repairerTeam->save();
            return $repairerTeam;
        }
    }
}

