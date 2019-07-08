@extends('voyager::master')

@section('page_title', __('voyager::generic.'.(isset($dataTypeContent->id) ? 'edit' : 'add')).' '.$dataType->display_name_singular)

@section('css')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@stop

@section('page_header')
    <h1 class="page-title">
        <i class="{{ $dataType->icon }}"></i>
        {{ __('voyager::generic.'.(isset($dataTypeContent->id) ? 'edit' : 'add')).' '.$dataType->display_name_singular }}
    </h1>
@stop

@section('content')
    <div class="page-content container-fluid">
        <form class="form-edit-add" role="form"
              action="@if(!is_null($dataTypeContent->getKey())){{ route('voyager.'.$dataType->slug.'.update', $dataTypeContent->getKey()) }}@else{{ route('voyager.'.$dataType->slug.'.store') }}@endif"
              method="POST" enctype="multipart/form-data" autocomplete="off">
            <!-- PUT Method if we are editing -->
            @if(isset($dataTypeContent->id))
                {{ method_field("PUT") }}
            @endif
            {{ csrf_field() }}

            <div class="row">
                <div class="col-md-8">
                    <div class="panel panel-bordered">
                    {{-- <div class="panel"> --}}
                        @if (count($errors) > 0)
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="panel-body">
                            @can('editRoles', $dataTypeContent)
                                <div class="form-group">
                                    <label for="default_role">{{ __('voyager::profile.role_default') }}</label>
                                    @php
                                        $dataTypeRows = $dataType->{(isset($dataTypeContent->id) ? 'editRows' : 'addRows' )};

                                        $row     = $dataTypeRows->where('field', 'user_belongsto_role_relationship')->first();
                                        $options = $row->details;
                                        $roles = TCG\Voyager\Models\Role::
                                        whereNotIn('id', [1,6])->get();
                                        
                                    @endphp
                                    @if(Auth::user()->role_id == '6')
                                        <select class="role_custom_drop form-control select2-ajax select2-hidden-accessible" name="role_id"   tabindex="-1" aria-hidden="true">
                                           <option value="" disabled>{{__('voyager::generic.none')}}</option>
                                            @foreach($roles as $role)
                                                <option @if($dataTypeContent->role_id == $role->id) selected @endif value="{{$role->id}}">{{$role->name}}</option>
                                            @endforeach
                                        </select>
                                    @else
                                        @include('voyager::formfields.relationship')
                                    @endif    
                                </div>
                            @endcan

                            <div class="form-group">
                                <label for="name">{{ __('voyager::generic.name') }}</label>
                                <input type="text" class="form-control" id="name" name="name" placeholder="{{ __('voyager::generic.name') }}"
                                       value="{{ $dataTypeContent->name ?? '' }}">
                            </div>

                            <div class="form-group">
                                <label for="email">{{ __('voyager::generic.email') }}</label>
                                <input type="email" class="form-control" id="email" name="email" placeholder="{{ __('voyager::generic.email') }}"
                                       value="{{ $dataTypeContent->email ?? '' }}">
                            </div>

                            <div class="form-group">
                                <label for="password">{{ __('voyager::generic.password') }}</label>
                                @if(isset($dataTypeContent->password))
                                    <br>
                                    <small>{{ __('voyager::profile.password_hint') }}</small>
                                @endif
                                <input type="password" class="form-control" id="password" name="password" value="" autocomplete="new-password">
                            </div>

                            @can('editRoles', $dataTypeContent)
                                <div class="form-group">
                                    <label for="parent">Parent</label>
                                    @php
                                        $row     = $dataTypeRows->where('field', 'user_belongsto_user_relationship')->first();
                                        $options = $row->details;
                                        $parentUsers = TCG\Voyager\Models\User::all();
                                    @endphp
                                    <select class="parent_user_drop form-control select2-ajax select2-hidden-accessible" name="parent_id"   tabindex="-1" aria-hidden="true">
                                        <option value="0">{{__('voyager::generic.none')}}</option>
                                        @foreach($parentUsers as $parentUser)
                                            <option  @if($dataTypeContent->parent_id == $parentUser->id) selected @endif value="{{$parentUser->id}}">{{$parentUser->name}}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="city">City</label>
                                    @php
                                        $cityArray = array();
                                        if($dataTypeContent->id && $dataTypeContent->parent_id){
                                            $cities = App\UserCityAssign::join('cities', 'user_city_assigns.city_id', '=', 'cities.id')->where("user_id",$dataTypeContent->parent_id)
                                            ->select('cities.id','cities.name')->get();
                                        }else{
                                            $cities = App\City::all();
                                        }

                                        $selectedCities = App\UserCityAssign::where('user_id',$dataTypeContent->id)->get();
                                        
                                        if(!$selectedCities->isEmpty()){
                                            foreach($selectedCities as $selectedCity){
                                                array_push($cityArray,$selectedCity->city_id);
                                            }
                                        }
                                    @endphp
                                    <select class="city_drop form-control select2-ajax select2-hidden-accessible" name="user_belongstomany_city_relationship[]"   tabindex="-1" aria-hidden="true" multiple="multiple">
                                        <option value="0">{{__('voyager::generic.none')}}</option>
                                        @foreach($cities as $city)
                                            <option @if(in_array($city->id, $cityArray))selected @endif value="{{$city->id}}">{{$city->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                              
                            @endcan
                            @php
                            if (isset($dataTypeContent->locale)) {
                                $selected_locale = $dataTypeContent->locale;
                            } else {
                                $selected_locale = config('app.locale', 'en');
                            }

                            @endphp
                            <div class="form-group">
                                <label for="locale">{{ __('voyager::generic.locale') }}</label>
                                <select class="form-control select2" id="locale" name="locale">
                                    @foreach (Voyager::getLocales() as $locale)
                                    <option value="{{ $locale }}"
                                    {{ ($locale == $selected_locale ? 'selected' : '') }}>{{ $locale }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="panel panel panel-bordered panel-warning">
                        <div class="panel-body">
                            <div class="form-group">
                                @if(isset($dataTypeContent->avatar))
                                    <img src="{{ filter_var($dataTypeContent->avatar, FILTER_VALIDATE_URL) ? $dataTypeContent->avatar : Voyager::image( $dataTypeContent->avatar ) }}" style="width:200px; height:auto; clear:both; display:block; padding:2px; border:1px solid #ddd; margin-bottom:10px;" />
                                @endif
                                <input type="file" data-name="avatar" name="avatar">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary pull-right save">
                {{ __('voyager::generic.save') }}
            </button>
        </form>

        <iframe id="form_target" name="form_target" style="display:none"></iframe>
        <form id="my_form" action="{{ route('voyager.upload') }}" target="form_target" method="post" enctype="multipart/form-data" style="width:0px;height:0;overflow:hidden">
            {{ csrf_field() }}
            <input name="image" id="upload_file" type="file" onchange="$('#my_form').submit();this.value='';">
            <input type="hidden" name="type_slug" id="type_slug" value="{{ $dataType->slug }}">
        </form>
    </div>
@stop

@section('javascript')
    <script>
        $('document').ready(function () {
            $(".role_custom_drop").select2({});
            $(".parent_user_drop").select2({});
            $(".city_drop").select2({});
            $('.toggleswitch').bootstrapToggle();
            $(".parent_user_drop").change(function(){
                var val = $(this).val();
                var url = '{{ route("get_parent_city") }}';
                $.ajax({
                    url: url,
                    type: 'POST',
                    data: {'parent_id' : val },
                    cache: false,
                    success: function(result){
                        $('.city_drop')
                            .find('option')
                            .remove()
                            .end();
                        $.each(JSON.parse(result), function( key, value ) {
                            $(".city_drop").append(new Option(value.name, value.id));
                        });
                    }
                });
            });
        });
    </script>
@stop
