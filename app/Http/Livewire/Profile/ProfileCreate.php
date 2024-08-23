<?php

namespace App\Http\Livewire\Profile;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use App\Models\Vendor;
use App\Models\User;
use App\Models\State;
use App\Models\lga;
// Image Intervention
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ProfileCreate extends Component
{
    use WithFileUploads;

    public $firstname,$lastname, $middlename, $email, $phone;
    public $profile_image;
    // public $signature;
    public $profileImagePreview; 
    // public $signaturePreview;
    public $dob;
    public $gender;
    public $marital_status;
    public $identification_mode;
    public $identification_no;
    public $current_location;
    public $permanent_address;
    public $community;
    public $business_name;
    public $business_address;
    public $business_email;
    public $business_mobile;
    public $business_type;
    public $registration_no;
    public $bank;
    public $account_name;
    public $account_no;
    public $tin;
    public $state_id;
    public $lga_id;
    public $states;
    public $lgas = [];

    public function mount()
    {
        $this->user = Auth::user();
        $this->firstname = $this->user->firstname;
        $this->lastname = $this->user->lastname;
        $this->middlename = $this->user->middlename;
        $this->email = $this->user->email;
        $this->phone = $this->user->phone;

        $this->states = State::all();
    }

    public function updatedStateId($value)
    {
        $this->lgas = Lga::where('state_id', $value)->get();
        $this->lga_id = null;
    }

    public function updatedProfileImage()
    {
        // Generate a preview of the uploaded profile picture
        $this->profileImagePreview = $this->profile_image->temporaryUrl();
    }

    // public function updatedSignature()
    // {
    //     // Generate a preview of the uploaded signature
    //     $this->signaturePreview = $this->signature->temporaryUrl();
    // }

    public function create()
    {
        $this->validate([
            'firstname' => 'required',
            'lastname' => 'required',
            'phone' => 'required|digits:11|unique:users,phone,' . $this->user->id,
            'email' => 'nullable|email|unique:users,email,' . $this->user->id,
            'dob' => 'required',
            'gender' => 'required',
            'marital_status' => 'required',
            'identification_mode' => 'required',
            'identification_no' => 'required',
            'state_id' => 'required|exists:states,id',
            'lga_id' => 'required|exists:lgas,id',
            'current_location' => 'required',
            'permanent_address' => 'required',
            'state_id' => 'required',
            'lga_id' => 'required',
            'community' => 'required',
            'business_name' => 'required',
            'business_address' => 'required',
            'business_email' => 'required',
            'business_mobile' => 'required',
            'business_type' => 'required',
            'registration_no' => 'required',
            'bank' => 'required',
            'account_name' => 'required',
            'account_no' => 'required',
            'tin' => 'required',
            'profile_image' => 'nullable|image|max:1024',
               
        ]);

        if ($this->profile_image) {
            
            $manager = new ImageManager(new Driver());

            $name_gen = hexdec(uniqid()).'.'.$this->profile_image->getClientOriginalExtension();
            $img = $manager->read($this->profile_image);
            $img = $img->resize(715,703);

            $img->toJpeg(80)->save(base_path('public/storage/profile_images/'.$name_gen));
            $save_url = 'profile_img/'.$name_gen;

        }
        // if ($this->signature) {
                
        //     $manager = new ImageManager(new Driver());

        //     $sig_name_gen = hexdec(uniqid()).'.'.$this->signature->getClientOriginalExtension();
        //     $img = $manager->read($this->signature);
        //     $img = $img->resize(715,303);

        //     $img->toJpeg(80)->save(base_path('public/storage/profile_sign/'.$sig_name_gen));
        //     $save_prof_url = 'profile_sign/'.$sig_name_gen;

        // }

        $vendor = Vendor::create([
            'user_id' => Auth::id(), 
            'dob' => $this->dob,
            'gender' => $this->gender,
            'marital_status' => $this->marital_status,
            'identification_mode' => $this->identification_mode,
            'identification_no' => $this->identification_no,
            'current_location' => $this->current_location,
            'permanent_address' => $this->permanent_address,
            'state_id' => $this->state_id,
            'lga_id' => $this->lga_id,
            'community' => $this->community,
            'business_name' => $this->business_name,
            'business_address' => $this->business_address,
            'business_email' => $this->business_email,
            'business_mobile' => $this->business_mobile,
            'business_type' => $this->business_type,
            'registration_no' => $this->registration_no,
            'bank' => $this->bank,
            'account_name' => $this->account_name,
            'account_no' => $this->account_no,
            'tin' => $this->tin, 
            
        ]);

        $this->user->update([
            'firstname' => $this->firstname,
            'lastname' => $this->lastname,
            'middlename' => $this->middlename,
            'email' => $this->email,
            'phone' => $this->phone,
            'profile_image'=> $save_url,
            // 'signature'=> $save_prof_url,
            'profile_completed' => 1,
        ]);


        return redirect()->route('vendor.profile.index')->with('message', 'Profile Updated');

    }

    public function render()
    {
        return view('livewire.profile.profile-create');
    }
}
