@extends('layouts.app')

@section('page-title')
    <div class="row bg-title">
        <!-- .page title -->
        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title"><i class="{{ $pageIcon }}"></i> {{ $pageTitle }}</h4>
        </div>
        <!-- /.page title -->
        <!-- .breadcrumb -->
        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
                <li><a href="{{ route('admin.dashboard') }}">@lang('app.menu.home')</a></li>
                <li class="active">{{ $pageTitle }}</li>
            </ol>
        </div>
        <!-- /.breadcrumb -->
    </div>
@endsection

@push('head-script')
    <link rel="stylesheet" href="{{ asset('plugins/bower_components/custom-select/custom-select.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/bower_components/bootstrap-select/bootstrap-select.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/bower_components/switchery/dist/switchery.min.css') }}">
@endpush

@section('content')

    <div class="row">
        @if($global->hide_cron_message == 0)
            <div class="col-md-12">
                <div class="alert alert-info">
                    <h5 class="text-white">Set following cron command on your server (Ignore if already done)</h5>
                    <code>* * * * * cd {{ base_path() }} && php artisan schedule:run >> /dev/null 2>&1</code>
                </div>
            </div>
        @endif
        <div class="col-md-12">
            <div class="panel panel-inverse">
                <div class="panel-heading">
                    @lang('modules.accountSettings.updateTitle')
                    @if($cachedFile)

                        <a href="javascript:;" id="clear-cache" class="btn btn-sm btn-danger pull-right m-l-5"><i
                                    class="fa fa-times"></i> @lang('app.disableCache')</a>
                        <h6 class="text-white pull-right m-r-5">@lang('messages.cacheEnabled')</h6>
                    @else


                        <a href="javascript:;" id="refresh-cache" class="btn btn-sm btn-success pull-right">
                            <i
                                    class="fa fa-check"></i> @lang('app.enableCache')</a>
                        <h6 class="text-white pull-right m-r-5">@lang('messages.cacheDisabled')</h6>
                    @endif

                </div>

                <div class="vtabs customvtab m-t-10">

                    @include('sections.admin_setting_menu')

                    <div class="tab-content">
                        <div id="vhome3" class="tab-pane active">
                            <div class="row">
                                <div class="col-sm-12 col-xs-12">
                                    {!! Form::open(['id'=>'editSettings','class'=>'ajax-form','method'=>'PUT']) !!}
                                    <div class="form-group">
                                        <label for="company_name">@lang('modules.accountSettings.companyName')</label>
                                        <input type="text" class="form-control" id="company_name" name="company_name"
                                               value="{{ $global->company_name }}">
                                    </div>
                                    <div class="form-group">
                                        <label for="company_email">@lang('modules.accountSettings.companyEmail')</label>
                                        <input type="email" class="form-control" id="company_email" name="company_email"
                                               value="{{ $global->company_email }}">
                                    </div>
                                    <div class="form-group">
                                        <label for="company_phone">@lang('modules.accountSettings.companyPhone')</label>
                                        <input type="tel" class="form-control" id="company_phone" name="company_phone"
                                               value="{{ $global->company_phone }}">
                                    </div>
                                    <div class="form-group">
                                        <label for="exampleInputPassword1">@lang('modules.accountSettings.companyWebsite')</label>
                                        <input type="text" class="form-control" id="website" name="website"
                                               value="{{ $global->website }}">
                                    </div>
                                    <div class="form-group">
                                        <label for="exampleInputPassword1">@lang('modules.accountSettings.companyLogo')</label>

                                        <div class="col-md-12">
                                            <div class="fileinput fileinput-new" data-provides="fileinput">
                                                <div class="fileinput-new thumbnail"
                                                     style="width: 200px; height: 150px;">
                                                    @if(is_null($global->logo))
                                                        <img src="http://via.placeholder.com/200x150.png?text=@lang('modules.accountSettings.uploadLogo')"
                                                             alt=""/>
                                                    @else
                                                        <img src="{{ $global->logo_url }}"
                                                             alt=""/>
                                                    @endif
                                                </div>
                                                <div class="fileinput-preview fileinput-exists thumbnail"
                                                     style="max-width: 200px; max-height: 150px;"></div>
                                                <div>
                                <span class="btn btn-info btn-file">
                                    <span class="fileinput-new"> @lang('app.selectImage') </span>
                                    <span class="fileinput-exists"> @lang('app.change') </span>
                                    <input type="file" name="logo" id="logo"> </span>
                                                    <a href="javascript:;" class="btn btn-danger fileinput-exists"
                                                       data-dismiss="fileinput"> @lang('app.remove') </a>
                                                </div>
                                            </div>

                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="m-t-20">@lang('modules.themeSettings.loginScreenBackground')</label>

                                        <div class="col-md-12 m-b-20">
                                            <div class="fileinput fileinput-new" data-provides="fileinput">
                                                <div class="fileinput-new thumbnail"
                                                     style="width: 200px; height: 150px;">
                                                    @if(is_null($global->login_background))
                                                        <img src="http://via.placeholder.com/200x150.png?text=@lang('modules.themeSettings.uploadImage')"

                                                             alt=""/>
                                                    @else
                                                        <img src="{{ $global->login_background_url }}"
                                                             alt=""/>
                                                    @endif
                                                </div>
                                                <div class="fileinput-preview fileinput-exists thumbnail"
                                                     style="max-width: 200px; max-height: 150px;"></div>
                                                <div>
                                    <span class="btn btn-info btn-file">
                                    <span class="fileinput-new"> @lang('app.selectImage') </span>
                                    <span class="fileinput-exists"> @lang('app.change') </span>
                                    <input type="file" name="login_background" id="login_background"> </span>
                                                    <a href="javascript:;" class="btn btn-danger fileinput-exists"
                                                       data-dismiss="fileinput"> @lang('app.remove') </a>
                                                </div>
                                            </div>
                                            <div class="note">Recommended size: 1500 X 1056 (Pixels)</div>

                                        </div>
                                    </div>


                                    <div class="form-group">
                                        <label for="address">@lang('modules.accountSettings.companyAddress')</label>
                                        <textarea class="form-control" id="address" rows="5"
                                                  name="address">{{ $global->address }}</textarea>
                                    </div>

                                    <div class="form-group">
                                        <label for="address">@lang('modules.accountSettings.defaultCurrency')</label>
                                        <select name="currency_id" id="currency_id" class="form-control">
                                            @foreach($currencies as $currency)
                                                <option
                                                        @if($currency->id == $global->currency_id) selected @endif
                                                value="{{ $currency->id }}">{{ $currency->currency_symbol.' ('.$currency->currency_code.')' }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    {{-- <div class="row">
                                        <div class="col-sm-12 col-md-12 col-xs-12">
                                            <div class="form-group">
                                                <label for="google_map_key">@lang('modules.accountSettings.weatherKey')</label> <a href="https://darksky.net/dev" class="btn btn-xs btn-info btn-outline" target="_blank">@lang('app.generateKey')</a>
                                                <input type="tel" class="form-control" id="weather_key" name="weather_key"
                                                       value="{{ $global->weather_key }}">
                                            </div>
                                        </div>
                                    </div> --}}
                                    <div class="form-group">
                                        <label for="address">@lang('modules.accountSettings.defaultTimezone')</label>
                                        <select name="timezone" id="timezone" class="form-control select2">
                                            @foreach($timezones as $tz)
                                                <option @if($global->timezone == $tz) selected @endif>{{ $tz }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label for="address">@lang('modules.accountSettings.dateFormat')</label>
                                        <select name="date_format" id="date_format" class="form-control select2">
                                            <option value="d-m-Y" @if($global->date_format == 'd-m-Y') selected @endif >
                                                d-m-Y ({{ $dateObject->format('d-m-Y') }})
                                            </option>
                                            <option value="m-d-Y" @if($global->date_format == 'm-d-Y') selected @endif >
                                                m-d-Y ({{ $dateObject->format('m-d-Y') }})
                                            </option>
                                            <option value="Y-m-d" @if($global->date_format == 'Y-m-d') selected @endif >
                                                Y-m-d ({{ $dateObject->format('Y-m-d') }})
                                            </option>
                                            <option value="d.m.Y" @if($global->date_format == 'd.m.Y') selected @endif >
                                                d.m.Y ({{ $dateObject->format('d.m.Y') }})
                                            </option>
                                            <option value="m.d.Y" @if($global->date_format == 'm.d.Y') selected @endif >
                                                m.d.Y ({{ $dateObject->format('m.d.Y') }})
                                            </option>
                                            <option value="Y.m.d" @if($global->date_format == 'Y.m.d') selected @endif >
                                                Y.m.d ({{ $dateObject->format('Y.m.d') }})
                                            </option>
                                            <option value="d/m/Y" @if($global->date_format == 'd/m/Y') selected @endif >
                                                d/m/Y ({{ $dateObject->format('d/m/Y') }})
                                            </option>
                                            <option value="m/d/Y" @if($global->date_format == 'm/d/Y') selected @endif >
                                                m/d/Y ({{ $dateObject->format('m/d/Y') }})
                                            </option>
                                            <option value="Y/m/d" @if($global->date_format == 'Y/m/d') selected @endif >
                                                Y/m/d ({{ $dateObject->format('Y/m/d') }})
                                            </option>
                                            <option value="d-M-Y" @if($global->date_format == 'd-M-Y') selected @endif >
                                                d-M-Y ({{ $dateObject->format('d-M-Y') }})
                                            </option>
                                            <option value="d/M/Y" @if($global->date_format == 'd/M/Y') selected @endif >
                                                d/M/Y ({{ $dateObject->format('d/M/Y') }})
                                            </option>
                                            <option value="d.M.Y" @if($global->date_format == 'd.M.Y') selected @endif >
                                                d.M.Y ({{ $dateObject->format('d.M.Y') }})
                                            </option>
                                            <option value="d-M-Y" @if($global->date_format == 'd-M-Y') selected @endif >
                                                d-M-Y ({{ $dateObject->format('d-M-Y') }})
                                            </option>
                                            <option value="d M Y" @if($global->date_format == 'd M Y') selected @endif >
                                                d M Y ({{ $dateObject->format('d M Y') }})
                                            </option>
                                            <option value="d F, Y"
                                                    @if($global->date_format == 'd F, Y') selected @endif >d F, Y
                                                ({{ $dateObject->format('d F, Y') }})
                                            </option>
                                            <option value="D/M/Y" @if($global->date_format == 'D/M/Y') selected @endif >
                                                D/M/Y ({{ $dateObject->format('D/M/Y') }})
                                            </option>
                                            <option value="D.M.Y" @if($global->date_format == 'D.M.Y') selected @endif >
                                                D.M.Y ({{ $dateObject->format('D.M.Y') }})
                                            </option>
                                            <option value="D-M-Y" @if($global->date_format == 'D-M-Y') selected @endif >
                                                D-M-Y ({{ $dateObject->format('D-M-Y') }})
                                            </option>
                                            <option value="D M Y" @if($global->date_format == 'D M Y') selected @endif >
                                                D M Y ({{ $dateObject->format('D M Y') }})
                                            </option>
                                            <option value="d D M Y"
                                                    @if($global->date_format == 'd D M Y') selected @endif >d D M Y
                                                ({{ $dateObject->format('d D M Y') }})
                                            </option>
                                            <option value="D d M Y"
                                                    @if($global->date_format == 'D d M Y') selected @endif >D d M Y
                                                ({{ $dateObject->format('D d M Y') }})
                                            </option>
                                            <option value="dS M Y"
                                                    @if($global->date_format == 'dS M Y') selected @endif >dS M Y
                                                ({{ $dateObject->format('dS M Y') }})
                                            </option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="address">@lang('modules.accountSettings.timeFormat')</label>
                                        <select name="time_format" id="time_format" class="form-control select2">
                                            <option value="h:i A" @if($global->time_format == 'H:i A') selected @endif >
                                                12 Hour (6:20 PM)
                                            </option>
                                            <option value="h:i a" @if($global->time_format == 'H:i a') selected @endif >
                                                12 Hour (6:20 pm)
                                            </option>
                                            <option value="H:i" @if($global->time_format == 'H:i') selected @endif >24
                                                Hour (18:20)
                                            </option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="address">@lang('modules.accountSettings.changeLanguage')</label>
                                        <select name="locale" id="locale" class="form-control select2">
                                            <option @if($global->locale == "en") selected @endif value="en">English
                                            </option>
                                            @foreach($languageSettings as $language)
                                                <option value="{{ $language->language_code }}"
                                                        @if($global->locale == $language->language_code) selected @endif >
                                                    {{ $language->language_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    {{-- <div class="form-group">
                                        {{--<a href="javascript:;" id="getLoaction" class="btn btn-warning m-b-10"><i class="ti-location-pin"></i> @lang('modules.accountSettings.getLocation')</a>
                                        <label for="address">@lang('modules.accountSettings.getLocation')</label>

                                        <input type="text" class="form-control" id="gmap_geocoding_address">

                                    </div> --}}
                                    <div class="row">
                                        <div class="col-sm-12 col-md-12 col-xs-12">
                                            <div class="form-group">
                                                <label class="control-label">@lang('modules.accountSettings.appDebug')
                                                    <a class="mytooltip" href="javascript:void(0)"> <i
                                                                class="fa fa-info-circle"></i><span
                                                                class="tooltip-content5"><span
                                                                    class="tooltip-text3"><span
                                                                        class="tooltip-inner2">@lang('modules.accountSettings.appDebugInfo')</span></span></span></a></label>
                                                <div class="switchery-demo">
                                                    <input type="checkbox" id="app_debug" name="app_debug"
                                                           @if($global->app_debug == true) checked
                                                           @endif class="js-switch " data-color="#00c292"
                                                           data-secondary-color="#f96262"/>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-12 col-md-12 col-xs-12">
                                            <div class="form-group">
                                                <label class="control-label">@lang('modules.accountSettings.googleRecaptcha')
                                                    <a class="mytooltip" href="javascript:void(0)">
                                                        <i class="fa fa-info-circle"></i>
                                                        <span class="tooltip-content5">
                                                            <span class="tooltip-text3">
                                                                <span class="tooltip-inner2">
                                                                    @lang('modules.accountSettings.googleRecaptchaInfo')
                                                                </span>
                                                            </span>
                                                        </span>
                                                    </a>
                                                </label>
                                                <div class="switchery-demo">
                                                    <input type="checkbox" id="google_recaptcha" name="google_recaptcha"
                                                           @if($global->google_recaptcha == true) checked
                                                           @endif class="js-switch " data-color="#00c292"
                                                           data-secondary-color="#f96262"/>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row" id="google_recaptcha_key_div"
                                         @if($global->google_recaptcha == false) style="display: none;" @endif>
                                        <div class="col-sm-12 col-md-12 col-xs-12">
                                            <div class="form-group">
                                                <label for="google_recaptcha_key">@lang('modules.accountSettings.googleRecaptchaKey')</label>
                                                <input type="text" class="form-control" id="google_recaptcha_key"
                                                       name="google_recaptcha_key"
                                                       value="{{ $global->google_recaptcha_key }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row" id="google_recaptcha_secret_div"
                                         @if($global->google_recaptcha == false) style="display: none;" @endif>
                                        <div class="col-sm-12 col-md-12 col-xs-12">
                                            <div class="form-group">
                                                <label for="google_recaptcha_secret">@lang('modules.accountSettings.googleRecaptchaSecret')</label>
                                                <input type="text" class="form-control" id="google_recaptcha_secret"
                                                       name="google_recaptcha_secret"
                                                       value="{{ $global->google_recaptcha_secret }}">
                                            </div>
                                        </div>
                                    </div>

                                    <button type="submit" id="save-form"
                                            class="btn btn-success waves-effect waves-light m-r-10">
                                        @lang('app.update')
                                    </button>
                                    <button type="reset" id="reset"
                                            class="btn btn-inverse waves-effect waves-light">@lang('app.reset')</button>
                                    {!! Form::close() !!}
                                </div>
                            </div>

                            <div class="clearfix"></div>
                        </div>
                    </div>
                </div>

            </div>
        </div>


    </div>
    <!-- .row -->

@endsection

@push('footer-script')
    <script src="{{ asset('plugins/bower_components/custom-select/custom-select.min.js') }}"></script>
    <script src="{{ asset('plugins/bower_components/bootstrap-select/bootstrap-select.min.js') }}"></script>
    <script src="{{ asset('plugins/bower_components/switchery/dist/switchery.min.js') }}"></script>

    <script>
        // Switchery
        var elems = Array.prototype.slice.call(document.querySelectorAll('.js-switch'));
        $('.js-switch').each(function () {
            new Switchery($(this)[0], $(this).data());

        });

        var changeCheckbox = document.getElementById('google_recaptcha');

        changeCheckbox.onchange = function () {
            if (changeCheckbox.checked) {
                $('#google_recaptcha_key_div').show();
                $('#google_recaptcha_secret_div').show();
            } else {
                // $('#google_recaptcha_key').val('');
                // $('#google_recaptcha_secret').val('');

                $('#google_recaptcha_key_div').hide();
                $('#google_recaptcha_secret_div').hide();
            }
        };

        $(".select2").select2({
            formatNoMatches: function () {
                return "{{ __('messages.noRecordFound') }}";
            }
        });

        $('#refresh-cache').click(function () {
            $.easyAjax({
                url: '{{url("refresh-cache")}}',
                type: "GET",
                success: function () {
                    window.location.reload();
                }
            })
        });

        $('#clear-cache').click(function () {
            $.easyAjax({
                url: '{{url("clear-cache")}}',
                type: "GET",
                success: function () {
                    window.location.reload();
                }
            })
        });

        $('#save-form').click(function () {
            $.easyAjax({
                url: '{{route('admin.settings.update', ['1'])}}',
                container: '#editSettings',
                type: "POST",
                redirect: true,
                file: true
            })
        });

        $(document).ready(function () {
            $("#getLoaction").click(function () {
                $('body').block({
                    message: '<p style="margin:0;padding:8px;font-size:24px;">Just a moment...</p>'
                    , css: {
                        color: '#fff'
                        , border: '1px solid #fb9678'
                        , backgroundColor: '#fb9678'
                    }
                });

                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(showPosition);
                } else {
                    alert("Geolocation is not supported by this browser.");
                    $("#locationMsg").html('');
                }
            });
        });


        function showPosition(position) {
            $('#latitude').val(position.coords.latitude);
            $('#longitude').val(position.coords.longitude);
            $('body').unblock();
        }

        $('#reset').click(function () {
            $('#locale').val('{{ $global->locale }}').trigger('change');
            $('#time_format').val('{{ $global->time_format }}').trigger('change');
            $('#timezone').val('{{ $global->timezone }}').trigger('change');
            $('#date_format').val('{{ $global->date_format }}').trigger('change');
        })

    </script>
@endpush

