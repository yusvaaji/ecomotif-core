@extends('admin.master_layout')
@section('title')
    <title>{{ $title }}</title>
@endsection

@section('body-header')
    <h3 class="crancy-header__title m-0">{{ $title }}</h3>
    <p class="crancy-header__text">{{ __('translate.Manage User') }} >> {{ $title }}</p>
@endsection

@section('body-content')
    <!-- crancy Dashboard -->
    <section class="crancy-adashboard crancy-show">
        <div class="container container__bscreen">
            <div class="row">
                <div class="col-12">
                    <div class="crancy-body">
                        <div class="crancy-dsinner">
                            <div class="row">
                                <div class="col-12">
                                    <div class="crancy-card">
                                        <div class="crancy-card__header">
                                            <h3 class="crancy-card__title">{{ __('translate.Create New User') }}</h3>
                                            <div class="crancy-card__header-right">
                                                <a href="{{ route('admin.user-list') }}" class="crancy-btn crancy-btn__v2">
                                                    <i class="fas fa-arrow-left"></i> {{ __('translate.Back to List') }}
                                                </a>
                                            </div>
                                        </div>
                                        <div class="crancy-card__body">
                                            <form action="{{ route('admin.user-store') }}" method="POST">
                                                @csrf
                                                
                                                <!-- User Type Selection -->
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <div class="form-group">
                                                            <label class="form-label">{{ __('translate.User Type') }} <span class="text-danger">*</span></label>
                                                            <select name="user_type" id="user_type" class="form-control" required>
                                                                <option value="user">{{ __('translate.Regular User') }}</option>
                                                                <option value="dealer">{{ __('translate.Dealer/Showroom') }}</option>
                                                                <option value="mediator">{{ __('translate.Mediator') }}</option>
                                                            </select>
                                                            <small class="form-text text-muted">{{ __('translate.Select the type of user to create') }}</small>
                                                        </div>
                                                    </div>
                                                </div>

                                                <hr>

                                                <!-- Basic Information -->
                                                <h5 class="mb-3">{{ __('translate.Basic Information') }}</h5>
                                                
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label class="form-label">{{ __('translate.Name') }} <span class="text-danger">*</span></label>
                                                            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                                                            <small class="form-text text-muted">✓ Required field</small>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label class="form-label">{{ __('translate.Email') }} <span class="text-danger">*</span></label>
                                                            <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                                                            <small class="form-text text-muted">✓ Required, must be unique</small>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label class="form-label">{{ __('translate.Password') }} <span class="text-danger">*</span></label>
                                                            <input type="password" name="password" class="form-control" required>
                                                            <small class="form-text text-muted">✓ Required, minimum 4 characters</small>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label class="form-label">{{ __('translate.Confirm Password') }} <span class="text-danger">*</span></label>
                                                            <input type="password" name="password_confirmation" class="form-control" required>
                                                            <small class="form-text text-muted">✓ Must match password</small>
                                                        </div>
                                                    </div>
                                                </div>

                                                <hr>

                                                <!-- Contact Information -->
                                                <h5 class="mb-3">{{ __('translate.Contact Information') }}</h5>
                                                
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label class="form-label">{{ __('translate.Phone') }}</label>
                                                            <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
                                                            <small class="form-text text-muted">Optional</small>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label class="form-label">{{ __('translate.Country') }}</label>
                                                            <input type="text" name="country" class="form-control" value="{{ old('country') }}">
                                                            <small class="form-text text-muted">Optional</small>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <div class="form-group">
                                                            <label class="form-label">{{ __('translate.Address') }}</label>
                                                            <textarea name="address" class="form-control" rows="3">{{ old('address') }}</textarea>
                                                            <small class="form-text text-muted">Optional</small>
                                                        </div>
                                                    </div>
                                                </div>

                                                <hr>

                                                <!-- Additional Information -->
                                                <h5 class="mb-3">{{ __('translate.Additional Information') }}</h5>
                                                
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label class="form-label">{{ __('translate.Designation') }}</label>
                                                            <input type="text" name="designation" class="form-control" value="{{ old('designation') }}">
                                                            <small class="form-text text-muted">Optional - Job title or position</small>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6" id="showroom_field" style="display: none;">
                                                        <div class="form-group">
                                                            <label class="form-label">{{ __('translate.Showroom') }}</label>
                                                            <select name="showroom_id" class="form-control">
                                                                <option value="">{{ __('translate.Select Showroom') }}</option>
                                                                @foreach($showrooms as $showroom)
                                                                    <option value="{{ $showroom->id }}" {{ old('showroom_id') == $showroom->id ? 'selected' : '' }}>
                                                                        {{ $showroom->name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                            <small class="form-text text-muted">Required for Mediator (optional)</small>
                                                        </div>
                                                    </div>
                                                </div>

                                                <hr>

                                                <!-- Status Settings -->
                                                <h5 class="mb-3">{{ __('translate.Status Settings') }}</h5>
                                                
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label class="form-label">{{ __('translate.Status') }}</label>
                                                            <select name="status" class="form-control">
                                                                <option value="enable" {{ old('status', 'enable') == 'enable' ? 'selected' : '' }}>{{ __('translate.Active') }}</option>
                                                                <option value="disable" {{ old('status') == 'disable' ? 'selected' : '' }}>{{ __('translate.Inactive') }}</option>
                                                            </select>
                                                            <small class="form-text text-muted">Default: Active</small>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label class="form-label">{{ __('translate.Banned Status') }}</label>
                                                            <select name="is_banned" class="form-control">
                                                                <option value="no" {{ old('is_banned', 'no') == 'no' ? 'selected' : '' }}>{{ __('translate.Not Banned') }}</option>
                                                                <option value="yes" {{ old('is_banned') == 'yes' ? 'selected' : '' }}>{{ __('translate.Banned') }}</option>
                                                            </select>
                                                            <small class="form-text text-muted">Default: Not Banned</small>
                                                        </div>
                                                    </div>
                                                </div>

                                                <hr>

                                                <!-- Data Checklist -->
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <div class="alert alert-info">
                                                            <h6><strong>{{ __('translate.Data Checklist') }}</strong></h6>
                                                            <ul class="mb-0" id="data_checklist">
                                                                <li>✓ Name: <span id="check_name" class="text-danger">Not filled</span></li>
                                                                <li>✓ Email: <span id="check_email" class="text-danger">Not filled</span></li>
                                                                <li>✓ Password: <span id="check_password" class="text-danger">Not filled</span></li>
                                                                <li>✓ Password Confirmation: <span id="check_password_confirmation" class="text-danger">Not filled</span></li>
                                                                <li>✓ User Type: <span id="check_user_type" class="text-success">Selected</span></li>
                                                                <li>✓ Phone: <span id="check_phone" class="text-muted">Optional</span></li>
                                                                <li>✓ Address: <span id="check_address" class="text-muted">Optional</span></li>
                                                                <li>✓ Country: <span id="check_country" class="text-muted">Optional</span></li>
                                                                <li>✓ Designation: <span id="check_designation" class="text-muted">Optional</span></li>
                                                                <li id="check_showroom_li" style="display: none;">✓ Showroom: <span id="check_showroom" class="text-muted">Optional</span></li>
                                                                <li>✓ Status: <span id="check_status" class="text-success">Set</span></li>
                                                                <li>✓ Banned Status: <span id="check_banned" class="text-success">Set</span></li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Submit Button -->
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <button type="submit" class="crancy-btn crancy-btn__v1">
                                                            <i class="fas fa-save"></i> {{ __('translate.Create User') }}
                                                        </button>
                                                        <a href="{{ route('admin.user-list') }}" class="crancy-btn crancy-btn__v2">
                                                            {{ __('translate.Cancel') }}
                                                        </a>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('js_section')
<script>
    $(document).ready(function() {
        // Show/hide showroom field based on user type
        $('#user_type').on('change', function() {
            if ($(this).val() == 'mediator') {
                $('#showroom_field').show();
                $('#check_showroom_li').show();
            } else {
                $('#showroom_field').hide();
                $('#check_showroom_li').hide();
            }
        });

        // Trigger on page load
        $('#user_type').trigger('change');

        // Real-time checklist validation
        $('input[name="name"]').on('input', function() {
            if ($(this).val().length > 0) {
                $('#check_name').removeClass('text-danger').addClass('text-success').text('Filled');
            } else {
                $('#check_name').removeClass('text-success').addClass('text-danger').text('Not filled');
            }
        });

        $('input[name="email"]').on('input', function() {
            var email = $(this).val();
            if (email.length > 0 && email.includes('@')) {
                $('#check_email').removeClass('text-danger').addClass('text-success').text('Valid email');
            } else if (email.length > 0) {
                $('#check_email').removeClass('text-success').addClass('text-warning').text('Invalid email');
            } else {
                $('#check_email').removeClass('text-success text-warning').addClass('text-danger').text('Not filled');
            }
        });

        $('input[name="password"]').on('input', function() {
            var password = $(this).val();
            if (password.length >= 4) {
                $('#check_password').removeClass('text-danger').addClass('text-success').text('Valid (min 4 chars)');
            } else if (password.length > 0) {
                $('#check_password').removeClass('text-success').addClass('text-warning').text('Too short (min 4 chars)');
            } else {
                $('#check_password').removeClass('text-success text-warning').addClass('text-danger').text('Not filled');
            }
        });

        $('input[name="password_confirmation"]').on('input', function() {
            var password = $('input[name="password"]').val();
            var confirmation = $(this).val();
            if (confirmation.length > 0 && password == confirmation) {
                $('#check_password_confirmation').removeClass('text-danger').addClass('text-success').text('Match');
            } else if (confirmation.length > 0) {
                $('#check_password_confirmation').removeClass('text-success').addClass('text-warning').text('Not match');
            } else {
                $('#check_password_confirmation').removeClass('text-success text-warning').addClass('text-danger').text('Not filled');
            }
        });

        $('input[name="phone"]').on('input', function() {
            if ($(this).val().length > 0) {
                $('#check_phone').removeClass('text-muted').addClass('text-success').text('Filled');
            } else {
                $('#check_phone').removeClass('text-success').addClass('text-muted').text('Optional');
            }
        });

        $('textarea[name="address"]').on('input', function() {
            if ($(this).val().length > 0) {
                $('#check_address').removeClass('text-muted').addClass('text-success').text('Filled');
            } else {
                $('#check_address').removeClass('text-success').addClass('text-muted').text('Optional');
            }
        });

        $('input[name="country"]').on('input', function() {
            if ($(this).val().length > 0) {
                $('#check_country').removeClass('text-muted').addClass('text-success').text('Filled');
            } else {
                $('#check_country').removeClass('text-success').addClass('text-muted').text('Optional');
            }
        });

        $('input[name="designation"]').on('input', function() {
            if ($(this).val().length > 0) {
                $('#check_designation').removeClass('text-muted').addClass('text-success').text('Filled');
            } else {
                $('#check_designation').removeClass('text-success').addClass('text-muted').text('Optional');
            }
        });

        $('select[name="showroom_id"]').on('change', function() {
            if ($(this).val()) {
                $('#check_showroom').removeClass('text-muted').addClass('text-success').text('Selected');
            } else {
                $('#check_showroom').removeClass('text-success').addClass('text-muted').text('Optional');
            }
        });
    });
</script>
@endpush




