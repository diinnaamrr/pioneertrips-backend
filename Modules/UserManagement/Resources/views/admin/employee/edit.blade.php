@extends('adminmodule::layouts.master')

@section('title', translate('Update_Employee'))

@section('content')
    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid">
            <div class="d-flex flex-wrap justify-content-between gap-3 align-items-center mb-4">
                <h2 class="fs-22 text-capitalize">{{ translate('update_employee') }}</h2>
            </div>

            <form action="{{ route('admin.employee.update',  $employee->id) }}" id='myForm' method="post"
                  enctype="multipart/form-data">
                @csrf
                @method('put')
                <div class="card">
                    <div class="card-body">
                        <div class="row gy-4">
                            <div class="col-lg-8">
                                <h5 class="text-primary text-uppercase mb-4">{{ translate('general_information') }}
                                </h5>

                                <div class="row align-items-end">
                                    <div class="col-sm-6">
                                        <div class="mb-4">
                                            <label for="f_name"
                                                   class="mb-2 text-capitalize">{{ translate('first_name') }}</label>
                                            <input type="text" value="{{ $employee?->first_name }}" name="first_name"
                                                   id="f_name" class="form-control"
                                                   placeholder="{{ translate('Ex: Maximilian') }}" required>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="mb-4">
                                            <label for="l_name" class="mb-2">{{ translate('last_name') }}</label>
                                            <input type="text" value="{{ $employee?->last_name }}" name="last_name"
                                                   id="l_name" class="form-control"
                                                   placeholder="{{ translate('Ex: Schwarzmüller') }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-4">
                                            <label for="phone_number" class="mb-2">{{ translate('phone') }}</label>
                                            <input type="tel" pattern="[0-9]{1,14}" value="{{ $employee->phone }}"
                                                   id="phone_number" class="form-control w-100 text-dir-start"
                                                   placeholder="{{ translate('Ex: xxxxx xxxxxx') }}" required>
                                            <input type="hidden" id="phone_number-hidden-element" name="phone">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-4">
                                            <label for="address" class="mb-2">{{ translate('address') }}</label>
                                            <input type="text" name="address"
                                                   value="{{ $employeeAddress->address ?? '' }}" id="address"
                                                   class="form-control" placeholder="{{ translate('Ex: Dhaka') }}"
                                                   required>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="mb-4">
                                            <label for="identity_type"
                                                   class="mb-2">{{ translate('identity_type') }}</label>
                                            <select name="identification_type" class="js-select text-capitalize"
                                                    id="identity_type" required>
                                                <option value="passport"
                                                    {{ $employee->identification_type == 'passport' ? 'selected' : '' }}>
                                                    {{ translate('passport') }}</option>
                                                <option value="nid"
                                                    {{ $employee->identification_type == 'nid' ? 'selected' : '' }}>
                                                    {{ translate('NID') }}</option>
                                                <option value="driving_license"
                                                    {{ $employee->identification_type == 'driving_license' ? 'selected' : '' }}>
                                                    {{ translate('driving_license') }}</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="mb-4">
                                            <label for="identity_card_num"
                                                   class="mb-2">{{ translate('identity_number') }}</label>
                                            <input type="text" value="{{ $employee->identification_number }}"
                                                   name="identification_number" id="identity_card_num"
                                                   class="form-control"
                                                   placeholder="{{ translate('Ex: 3032') }}" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="d-flex flex-column justify-content-around gap-3">
                                    <h5 class="text-center text-capitalize">{{ translate('employee_image') }}</h5>

                                    <div class="d-flex justify-content-center">
                                        <div class="upload-file auto">
                                            <input type="file" name="profile_image" class="upload-file__input"
                                                   accept=".jpg, .jpeg, .png">
                                            <span class="edit-btn show">
                                                <img
                                                    src="{{ asset('public/assets/admin-module/img/svg/edit-circle.svg') }}"
                                                    alt="" class="svg">
                                            </span>
                                            <div
                                                class="upload-file__img border-gray d-flex justify-content-center align-items-center w-180 h-180 p-0">
                                                <img class="upload-file__img__img h-100" width="180" height="180"
                                                     loading="lazy"
                                                     src="{{ onErrorImage(
                                                        $employee?->profile_image,
                                                        asset('storage/app/public/employee/profile') . '/' . $employee?->profile_image,
                                                        asset('public/assets/admin-module/img/avatar/avatar.png'),
                                                        'employee/profile/',
                                                    ) }}"
                                                     alt="">
                                            </div>
                                        </div>
                                    </div>
                                    <p class="opacity-75 mx-auto max-w220">
                                        {{ translate('JPG, JPEG, PNG Less Than 1MB') }}
                                    </p>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="d-flex flex-column justify-content-around gap-3">
                                    <h5 class="text-capitalize">{{ translate('identity_card_image') }}</h5>
                                    <div class="d-flex gap-3 flex-wrap">
                                        @foreach ($employee->identification_image as $index => $img)
                                            <div class="remove-upload-file upload-file__img_banner mb-20 position-relative">
                                                <!-- Close button -->
                                                <button class="spartan_remove_row border-0 remove-old-image"
                                                        type="button">
                                                    <i class="tio-clear"></i>
                                                </button>
                                                <!-- Existing image -->
                                                <img
                                                    src="{{ asset('storage/app/public/employee/identity') }}/{{ $img }}"
                                                    class="existing_image"
                                                    style="width: 100%; height: 130px;">
                                                <input type="hidden" name="existing_identity_images[]"
                                                       value="{{ $img }}">
                                            </div>
                                        @endforeach
                                        {{--                                        @if(count($employee->identification_image)<3)--}}
                                        <div class="upload-file d-flex custom" id="multi_image_picker">

                                        </div>
                                        {{--                                        @endif--}}
                                    </div>

                                </div>
                            </div>

                        </div>
                    </div>

                </div>
                <div class="card mt-3">
                    <div class="card-body">
                        <h6 class="fw-semibold text-primary text-uppercase mb-4 text-capitalize">
                            {{ translate('employee_responsibility') }}</h6>
                        <div class="mb-4 max-w300">
                            <label for="employee-role" class="mb-2">{{ translate('employee_role') }}</label>
                            <select name="role_id" id="employee-role" class="form-control js-select" required>

                                <option>{{ translate('--Select_Role--') }}</option>

                                @forelse($roles as $role)
                                    <option value="{{ $role->id }}"
                                        {{ $employee->role_id == $role->id ? 'selected' : '' }}>{{ $role->name }}
                                    </option>
                                @empty
                                @endforelse
                            </select>
                        </div>

                        <div id="roles">
                            @if($employee?->role)
                                <h5 class="fw-semibold mt-5 mb-3 text-capitalize">{{ translate('module_access') }}</h5>
                                <div class="row g-3">
                                    <input type="hidden" name="role_id" value="{{ $role['id'] }}">
                                    @foreach ($employee->role['modules'] as $key => $module)
                                        <div class="col-lg-6">
                                            <div class="card">
                                                <div class="badge-primary p-3">
                                                    <label class="custom-checkbox">
                                                        <input type="checkbox" class="select-all-module"
                                                               id="select-all-{{ $key }}" data-module="{{ $key }}">
                                                        {{ translate($module) }}
                                                    </label>
                                                </div>
                                                <div
                                                    class="card-body d-flex flex-wrap align-items-center column-gap-4 row-gap-3">
                                                    @if (array_key_exists($module, MODULES))
                                                        @foreach (MODULES[$module] as $permission)
                                                            <label class="custom-checkbox">
                                                                <input type="checkbox"
                                                                       class="module-checkbox"
                                                                       data-module="{{ $key }}"
                                                                       name="permission[{{ $module }}][]"
                                                                       value="{{ $permission }}"
                                                                    {{ $employee->moduleAccess->where('module_name', $module)->first()?->$permission == 1 ? 'checked' : '' }}>
                                                                {{ translate($permission) }}
                                                            </label>
                                                        @endforeach
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>


                    </div>
                </div>
                <div class="card mt-3">
                    <div class="card-body">
                        <h5 class="text-primary text-uppercase mb-4">{{ translate('account_information') }}</h5>

                        <div class="row align-items-end">
                            <div class="col-sm-4">
                                <div class="mb-4">
                                    <label for="p_email" class="mb-2">{{ translate('email') }}</label>
                                    <input type="email" value="{{ $employee->email }}" name="email" id="p_email"
                                           class="form-control"
                                           placeholder="{{ translate('Ex: company@company.com') }}" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-4 input-group_tooltip">
                                    <label for="password" class="mb-2">{{ translate('password') }}</label>
                                    <input type="password" name="password" id="password" class="form-control"
                                           placeholder="{{ translate('Ex: ********') }}">
                                    <i id="password-eye" class="mt-3 bi bi-eye-slash-fill text-primary tooltip-icon"
                                       data-bs-toggle="tooltip" data-bs-title=""></i>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-4 input-group_tooltip">
                                    <label for="confirm_password"
                                           class="mb-2">{{ translate('confirm_password') }}</label>
                                    <input type="password" name="confirm_password" id="confirm_password"
                                           class="form-control" placeholder="{{ translate('Ex: ********') }}">
                                    <i id="conf-password-eye"
                                       class="mt-3 bi bi-eye-slash-fill text-primary tooltip-icon"
                                       data-bs-toggle="tooltip" data-bs-title=""></i>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="d-flex justify-content-end gap-3 mt-3">
                    <button class="btn btn-primary" type="submit">{{ translate('save') }}</button>
                </div>
            </form>
        </div>
    </div>

    <!-- End Main Content -->
    <input type="hidden" id="existingValue" value="{{count($employee->identification_image)}}">
@endsection

@push('script')
    <link href="{{ asset('public/assets/admin-module/css/intlTelInput.min.css') }}" rel="stylesheet"/>
    <script src="{{ asset('public/assets/admin-module/js/intlTelInput.min.js') }}"></script>
    <script src="{{ asset('public/assets/admin-module/js/spartan-multi-image-picker.js') }}"></script>
    <script src="{{ asset('public/assets/admin-module/js/password.js') }}"></script>

    <script>
        // Get all upload-file input elements
        document.querySelectorAll('.upload-file__input').forEach(function (input) {
            input.addEventListener('change', function (event) {
                var file = event.target.files[0];
                var card = event.target.closest('.upload-file');
                var textbox = card.querySelector('.upload-file__textbox');
                var imgElement = card.querySelector('.upload-file__img__img');

                if (file) {
                    var reader = new FileReader();
                    reader.onload = function (e) {
                        textbox.style.display = 'none';
                        imgElement.src = e.target.result;
                        imgElement.style.display = 'block';
                    };
                    reader.readAsDataURL(file);
                }
            });
        });
    </script>

    <script>
        "use strict";
        $(document).ready(function () {
            // Function to toggle all checkboxes within a module
            $(document).on('change', '.select-all-module', function () {
                let moduleKey = $(this).data('module');
                let isChecked = $(this).is(':checked');

                $('.module-checkbox[data-module="' + moduleKey + '"]').prop('checked', isChecked);
            });

            // Function to update the Select All checkbox based on individual checkboxes
            $(document).on('change', '.module-checkbox', function () {
                let moduleKey = $(this).data('module');
                let allCheckboxes = $('.module-checkbox[data-module="' + moduleKey + '"]');
                let allChecked = allCheckboxes.length === allCheckboxes.filter(':checked').length;
                $('#select-all-' + moduleKey).prop('checked', allChecked);
            });
            // Initialize "Select All" checkbox state on page load
            $('.select-all-module').each(function () {
                let moduleKey = $(this).data('module');
                let allCheckboxes = $('.module-checkbox[data-module="' + moduleKey + '"]');
                let allChecked = allCheckboxes.length === allCheckboxes.filter(':checked').length;
                $(this).prop('checked', allChecked);
            });
        });
    </script>

    <script>
        "use strict";
        initializePhoneInput("#phone_number", "#phone_number-hidden-element");
        getCount();
        $(".remove-old-image").on('click', function () {
            $(this).closest('.remove-upload-file').remove();
            getCount();
        })

        function getCount() {
            let inputFields = document.querySelectorAll('input[name="existing_identity_images[]"]');
            let data = Array.from(inputFields).filter(input => input.value.trim() !== '').length;

            let inputFields1 = document.querySelectorAll('input[name="identity_images[]"]');
            let data1 = Array.from(inputFields1).filter(input => input.value.trim() !== '').length;
            const maxCount = parseInt(data + data1);
            if (maxCount < 5) {
                $("#multi_image_picker .upload-file__img:last-child").removeClass('d-none');
            } else {

                $("#multi_image_picker .upload-file__img:last-child").addClass('d-none');
            }
            return parseInt(5 - data);
        }


        $("#multi_image_picker").spartanMultiImagePicker({
            fieldName: 'identity_images[]',
            maxCount: getCount(),
            rowHeight: '130px',
            groupClassName: 'upload-file__img upload-file__img_banner',
            placeholderImage: {
                image: "{{ asset('public/assets/admin-module/img/document-upload.png') }}",
                width: '34px',
            },
            dropFileLabel: `
                <h6 id="dropAreaLabel" class="mt-2 fw-semibold">
                    <span class="text-info">{{ translate('Click to upload') }}</span>
                    <br>
                    {{ translate('or drag and drop') }}
            </h6>`,
            onAddRow: function (index) {
                getCount();
            },

            onRenderedPreview: function (index) {
                if ($(".file_upload").find(".img_").length > 0) {
                    $("#dropAreaLabel").hide();
                }
                $(".file_upload").on("dragenter", function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    $(this).find('#dropAreaLabel').hide();
                    $(this).find('.spartan_image_placeholder').hide();
                });
                toastr.success('{{ translate('image_added') }}', {
                    CloseButton: true,
                    ProgressBar: true
                });
            },
            onRemoveRow: function (index) {
                getCount();
            },
            onExtensionErr: function (index, file) {
                toastr.error('{{ translate('please_only_input_png_or_jpg_type_file') }}', {
                    CloseButton: true,
                    ProgressBar: true
                });
            },
            onSizeErr: function (index, file) {
                toastr.error('{{ translate('file_size_too_big') }}', {
                    CloseButton: true,
                    ProgressBar: true
                });
            }
        });

        $(".file_upload").on("dragenter", function(e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).find('#dropAreaLabel').hide();
            $(this).find('.spartan_image_placeholder').hide();
        });

        $(".file_upload").on("dragover", function(e) {
            e.preventDefault();
            e.stopPropagation();
        });

        $("#employee-role").on('change', function () {
            let value = $(this).val()
            loadRoles(value)
        })

        function loadRoles(obj) {

            $.ajax({
                url: '{{ route('admin.employee.role.get-roles') }}',
                _method: 'PUT',
                data: {
                    id: obj
                },
                success: function (data) {

                    $('#roles').empty().html(data.view)
                },
            });
        }
    </script>
@endpush
