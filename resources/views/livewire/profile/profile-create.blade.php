<form wire:submit.prevent="create">
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    {{ $error }}
                @endforeach
            </ul>
        </div>
    @endif
    <div class="row g-4 pb-3">
        <div class="col-md-6">
            <!-- Profile Picture Upload -->
            <div class="form-group">
                <span class="text-danger">*</span> Profile Image</label>
                <input type="file" id="profile_image" wire:model="profile_image" class="form-control">
                @error('profile_image') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

        </div>
        <div class="col-md-6">
            @if ($profileImagePreview)
                <div class="mt-3">
                    <label>Preview:</label>
                    <img src="{{ $profileImagePreview }}" alt="Profile Image Preview" class="img-fluid img-thumbnail" style="max-width: 150px;">
                </div>
            @endif
        </div>
    </div>
    <div class="row g-4 pb-3">
        <div class="col-md-6">
            <!-- Profile Picture Upload -->
            <div class="form-floating mb-0">
                <input type="text" class="form-control" id="floatingInput" placeholder="First name" wire:model="firstname">
                <label for="floatingInput"><span class="text-danger">*</span> First Name</label>
                @error('firstname')
                    <span class="text-danger">{{ $message }} </span>
                @enderror
            </div>

        </div>
        <div class="col-md-6">
            <div class="form-floating mb-0">
                <input type="text" class="form-control" id="floatingInput" placeholder="Last Name" wire:model="lastname">
                <label for="floatingPassword"><span class="text-danger">*</span> Last Name</label>
                @error('lastname')
                <span class="text-danger">{{ $message }} </span>
                @enderror
            </div>
        </div>
    </div>
    <div class="row g-4 pb-3">
        <div class="col-md-6">
            <div class="form-floating mb-0">
                <input type="text" class="form-control" id="floatingInput" placeholder="Middle Name" wire:model="middlename">
                <label for="floatingInput">Middle Name</label>
                @error('middlename')
                <span class="text-danger">{{ $message }} </span>
                @enderror
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-floating mb-0">
                <input type="text" class="form-control" id="floatingInput" placeholder="Phone Number" wire:model="phone">
                <label for="floatingPassword"><span class="text-danger">*</span> Phone Number</label>
                @error('phone')
                <span class="text-danger">{{ $message }} </span>
                @enderror
            </div>
        </div>
    </div>
    <div class="row g-4 pb-3">
        <div class="col-md-6">
            <div class="form-floating mb-0">
                <input type="text" class="form-control" id="floatingInput" placeholder="Email Address" wire:model="email">
                <label for="floatingInput">Email Address</label>
                @error('email')
                <span class="text-danger">{{ $message }} </span>
                @enderror
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-floating mb-0">
                <input type="date" class="form-control" id="floatingInput" placeholder="Date Of Birth" wire:model="dob">
                <label for="floatingPassword"><span class="text-danger">*</span> Date Of Birth</label>
                @error('dob')
                <span class="text-danger">{{ $message }} </span>
                @enderror
            </div>
        </div>
    </div>
    <div class="row g-4 pb-3">
        <div class="col-md-6">
            <div class="form-floating mb-0">
                <select class="form-control" id="floatingselect1" aria-label="Floating label select example" wire:model="gender">
                <option selected>---Select---</option>
                <option value="male">Male</option>
                <option value="female">Female</option>
                </select>
                <label for="floatingselect1"><span class="text-danger">*</span> Gender</label>
                @error('gender')
                <span class="text-danger">{{ $message }} </span>
                @enderror
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-floating mb-0">
                <select class="form-control" id="floatingselect1" aria-label="Floating label select example" wire:model="marital_status">
                <option selected>---Select---</option>
                <option value="single">Single</option>
                <option value="married">Married</option>
                <option value="divorced">Divorced</option>
                </select>
                <label for="floatingselect1"><span class="text-danger">*</span> Marital Status</label>
                @error('marital_status')
                <span class="text-danger">{{ $message }} </span>
                @enderror
            </div>
        </div>
    </div>
    <div class="row g-4 pb-3">
        <div class="col-md-6">
            <div class="form-floating mb-0">
                <select class="form-control" id="floatingselect1" aria-label="Floating label select example" wire:model="identification_mode">
                <option selected>---Select---</option>
                <option value="bvn">BVN</option>
                <option value="nin">NIN</option>
                </select>
                <label for="floatingselect1"><span class="text-danger">*</span> Mode Of Identification</label>
                @error('identification_mode')
                <span class="text-danger">{{ $message }} </span>
                @enderror
            </div>
        </div>
        <div class="col-md-6">
        <div class="form-floating mb-0">
            <input type="text" class="form-control" id="floatingInput" placeholder="Identification Number" wire:model="identification_no">
            <label for="floatingPassword"><span class="text-danger">*</span> Identification Number</label>
            @error('identification_no')
                <span class="text-danger">{{ $message }} </span>
            @enderror
        </div>
        </div>
    </div>
    <h5 class="mt-3">Location</h5>
    <hr>
    <div class="row g-4 pb-3">
        <div class="col-md-6">
            <div class="form-floating mb-0">
            <input type="text" class="form-control" id="floatingInput" placeholder="Current Address" wire:model="current_location">
            <label for="floatingInput"><span class="text-danger">*</span> Current Address</label>
            @error('current_location')
            <span class="text-danger">{{ $message }} </span>
            @enderror
            </div>
            
        </div>
        <div class="col-md-6">
            <div class="form-floating mb-0">
                <input type="text" class="form-control" id="floatingInput" placeholder="Permanent Address" wire:model="permanent_address">
                <label for="floatingPassword">Permanent Address</label>
                @error('permanent_address')
                <span class="text-danger">{{ $message }} </span>
                @enderror
            </div>
        </div>
    </div>
    <div class="row g-4 pb-3">
        <div class="col-md-6">
            <div class="form-floating mb-0">
                <select class="form-control" id="identification" aria-label="Floating label select" wire:model="state_id">
                <option selected>---Select---</option>
                @foreach($states as $state)
                <option value="{{ $state->id }}">{{ $state->name }}</option>
                @endforeach
                </select>
                <label for="floatingselect1"><span class="text-danger">*</span> State</label>
                @error('state_id')
                <span class="text-danger">{{ $message }} </span>
                @enderror
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-floating mb-0">
                <select class="form-control" id="identification" aria-label="Floating label select" wire:model="lga_id">
                <option selected>---Select---</option>
                @foreach($lgas as $lga)
                <option value="{{ $lga->id }}">{{ $lga->name }}</option>
                @endforeach
                </select>
                <label for="floatingselect1"><span class="text-danger">*</span> Local Govenment Area</label>
                @error('lga_id')
                <span class="text-danger">{{ $message }} </span>
                @enderror
            </div>
        </div>
    </div>
    <div class="row g-4 pb-3">
        <div class="col-md-6">
            <div class="form-floating mb-0">
            <input type="text" class="form-control" id="floatingInput" placeholder="Community/village" wire:model="community">
            <label for="floatingInput"><span class="text-danger">*</span> Community/village</label>
            @error('community')
                <span class="text-danger">{{ $message }} </span>
                @enderror
            </div>
        </div>
    </div>
    <h5 class="mt-3">Business Information</h5>
    <hr>
    <div class="row g-4 pb-3">
        <div class="col-md-6">
            <div class="form-floating mb-0">
            <input type="text" class="form-control" id="floatingInput" placeholder="Business Name" wire:model="business_name">
            <label for="floatingInput"><span class="text-danger">*</span> Business Name</label>
            @error('business_name')
                <span class="text-danger">{{ $message }} </span>
                @enderror
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-floating mb-0">
                <input type="text" class="form-control" id="floatingInput" placeholder="Business Address" wire:model="business_address">
                <label for="floatingPassword"><span class="text-danger">*</span> Address</label>
                @error('business_address')
                <span class="text-danger">{{ $message }} </span>
                @enderror
            </div>
        </div>
    </div>
    <div class="row g-4 pb-3">
        <div class="col-md-6">
            <div class="form-floating mb-0">
            <input type="email" class="form-control" id="floatingInput" placeholder="Business Email" wire:model="business_email">
            <label for="floatingInput">Business Email</label>
            @error('business_email')
                <span class="text-danger">{{ $message }} </span>
                @enderror
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-floating mb-0">
            <input type="text" class="form-control" id="floatingInput" placeholder="Business Phone Number" wire:model="business_mobile">
            <label for="floatingInput"><span class="text-danger">*</span> Business Phone Number</label>
            @error('business_mobile')
                <span class="text-danger">{{ $message }} </span>
                @enderror
            </div>
        </div>
    </div>
    <div class="row g-4 pb-3">
        <div class="col-md-6">
            <div class="form-floating mb-0">
                <select class="form-control" id="identification" aria-label="Floating label select" wire:model="business_type">
                <option selected>---Select---</option>
                <option value="sole_proprietorship">Sole Proprietorship</option>
                <option value="partnership">Partnership</option>
                <option value="limited_liability">Limited Liability</option>
                </select>
                <label for="floatingselect1"><span class="text-danger">*</span> Business Type</label>
                @error('business_type')
                <span class="text-danger">{{ $message }} </span>
                @enderror
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-floating mb-0">
                <input type="text" class="form-control" id="floatingInput" placeholder="Registration Number" wire:model="registration_no">
                <label for="floatingPassword"><span class="text-danger">*</span> Registration Number</label>
                @error('registration_no')
                <span class="text-danger">{{ $message }} </span>
                @enderror
            </div>
        </div>
    </div>
    <div class="row g-4 pb-3">
        <div class="col-md-6">
            <div class="form-floating mb-0">
            <input type="text" class="form-control" id="floatingInput" placeholder="Bank Name" wire:model="bank">
            <label for="floatingInput"><span class="text-danger">*</span> Bank Name</label>
            @error('bank')
            <span class="text-danger">{{ $message }} </span>
            @enderror
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-floating mb-0">
            <input type="text" class="form-control" id="floatingInput" placeholder="Account Name" wire:model="account_name">
            <label for="floatingInput"><span class="text-danger">*</span> Account Name</label>
            @error('account_name')
            <span class="text-danger">{{ $message }} </span>
            @enderror
            </div>
        </div>
    </div>
    <div class="row g-4 pb-3">
        <div class="col-md-6">
            <div class="form-floating mb-0">
            <input type="text" class="form-control" id="floatingInput" placeholder="Account Number" wire:model="account_no">
            <label for="floatingInput"><span class="text-danger">*</span> Account Number</label>
            @error('account_no')
            <span class="text-danger">{{ $message }} </span>
            @enderror
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-floating mb-0">
            <input type="text" class="form-control" id="floatingInput" placeholder="Tax Identification Number (TIN)" wire:model="tin">
            <label for="floatingInput"><span class="text-danger">*</span> Tax Identification Number (TIN)</label>
            @error('tin')
            <span class="text-danger">{{ $message }} </span>
            @enderror
            </div>
        </div>

        <div class="col-md-6">
            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="exampleCheck1" required>
                <label for="exampleCheck1">I accept all Terms & Conditions and confirm that all information is accurate.</label>
            </div>
        </div>
        
        {{-- <div class="row g-4 pb-3">
            <div class="col-md-6">
                <!-- Profile Picture Upload -->
                <div class="form-group">
                    <span class="text-danger">*</span>Signature</label>
                    <input type="file" id="signature" wire:model="signature" class="form-control">
                    @error('signature') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
    
            </div>
            <div class="col-md-6">
                @if ($signaturePreview)
                    <div class="mt-3">
                        <label>Preview:</label>
                        <img src="{{ $signaturePreview }}" alt="Profile Image Preview" class="img-fluid img-thumbnail" style="max-width: 150px;">
                    </div>
                @endif
            </div>
        </div> --}}  
    </div>
    <button class="btn btn-primary" type="submit">Submit</button>
</form>

