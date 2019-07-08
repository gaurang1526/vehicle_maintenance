@php
    $edit = !is_null($dataTypeContent->getKey());
    $add  = is_null($dataTypeContent->getKey());
@endphp

@extends('voyager::master')

@section('css')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style type="text/css">
        .not_active {
          pointer-events: none;
          cursor: default;
        }
    </style>
@stop

@section('page_title', __('voyager::generic.'.($edit ? 'edit' : 'add')).' '.$dataType->display_name_singular)

@section('page_header')
    <h1 class="page-title">
        <i class="{{ $dataType->icon }}"></i>
        {{ __('voyager::generic.'.($edit ? 'edit' : 'add')).' '.$dataType->display_name_singular }}
    </h1>
    @include('voyager::multilingual.language-selector')
@stop

@section('content')
    <div class="page-content edit-add container-fluid">
        <div class="row">
            <div class="col-md-12">

                <div class="panel panel-bordered">
                    <!-- form start -->
                    <form role="form"
                            class="form-edit-add"
                            action="{{ $edit ? route('voyager.'.$dataType->slug.'.update', $dataTypeContent->getKey()) : route('voyager.'.$dataType->slug.'.store') }}"
                            method="POST" enctype="multipart/form-data">
                        <!-- PUT Method if we are editing -->
                        @if($edit)
                            {{ method_field("PUT") }}
                        @endif

                        <!-- CSRF TOKEN -->
                        {{ csrf_field() }}

                        <div class="panel-body">

                            @if (count($errors) > 0)
                                <div class="alert alert-danger">
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <!-- Adding / Editing -->
                            @php
                                $dataTypeRows = $dataType->{($edit ? 'editRows' : 'addRows' )};
                            @endphp

                            @foreach($dataTypeRows as $row)
                                <!-- GET THE DISPLAY OPTIONS -->
                                @php
                                    $display_options = $row->details->display ?? NULL;
                                    if ($dataTypeContent->{$row->field.'_'.($edit ? 'edit' : 'add')}) {
                                        $dataTypeContent->{$row->field} = $dataTypeContent->{$row->field.'_'.($edit ? 'edit' : 'add')};
                                    }
                                @endphp
                                @if (isset($row->details->legend) && isset($row->details->legend->text))
                                    <legend class="text-{{ $row->details->legend->align ?? 'center' }}" style="background-color: {{ $row->details->legend->bgcolor ?? '#f0f0f0' }};padding: 5px;">{{ $row->details->legend->text }}</legend>
                                @endif
                                <div class="form-group @if($row->type == 'hidden') hidden @endif col-md-{{ $display_options->width ?? 12 }} {{ $errors->has($row->field) ? 'has-error' : '' }}" @if(isset($display_options->id)){{ "id=$display_options->id" }}@endif>
                                    {{ $row->slugify }}
                                    <label class="control-label" for="name">{{ $row->display_name }}</label>
                                    @include('voyager::multilingual.input-hidden-bread-edit-add')
                                    @if (isset($row->details->view))
                                        @include($row->details->view, ['row' => $row, 'dataType' => $dataType, 'dataTypeContent' => $dataTypeContent, 'content' => $dataTypeContent->{$row->field}, 'action' => ($edit ? 'edit' : 'add')])
                                    @elseif ($row->type == 'relationship')
                                        @include('voyager::formfields.relationship', ['options' => $row->details])
                                    @else
                                        {!! app('voyager')->formField($row, $dataType, $dataTypeContent) !!}
                                    @endif

                                    @foreach (app('voyager')->afterFormFields($row, $dataType, $dataTypeContent) as $after)
                                        {!! $after->handle($row, $dataType, $dataTypeContent) !!}
                                    @endforeach
                                    @if ($errors->has($row->field))
                                        @foreach ($errors->get($row->field) as $error)
                                            <span class="help-block">{{ $error }}</span>
                                        @endforeach
                                    @endif
                                </div>
                            @endforeach

                            <!-- ////////////////////////////// -->
                                <div class= "vehicle_type_div">
                                    <label>Vehicle Type:</label>
                                    <select class="vehicle_type form-control" name="vehicle_type" id="vehicle_type">
                                        <option></option>
                                        @if(!empty($vehicleTypes))
                                        @foreach($vehicleTypes as $vehicleType)
                                            <option value="{{$vehicleType->id}}" @if(@ $vehicleTypeIdArray['vehicle_type_id'] == $vehicleType->id) selected @endif>{{$vehicleType->type}}</option>
                                        @endforeach
                                        @endif
                                    </select>
                                </div>
                                <div class="main_div">
                                    <div class="vehicle_main_div">
                                        @php $j=0; @endphp
                                        @if(@$finalArray)
                                            @foreach(@$finalArray as $array)
                                                <?php
                                                    $sel = '';
                                                    $serviceLists = DB::table('vehicle_type_service_assigns')
                                                    ->select("services.id","services.name")
                                                    ->join("vehicle_brand_multiple_service_assigns","vehicle_brand_multiple_service_assigns.vehicle_type_service_assigns_id","=",
                                                    "vehicle_type_service_assigns.id")
                                                    ->join("services","services.id","=","vehicle_brand_multiple_service_assigns.service_id")
                                                    ->where("vehicle_type_id", $vehicleTypeIdArray['vehicle_type_id'])
                                                    ->where("vehicle_brand_id",$array['vehicle_brand_id'])->get();
                                                ?>
                                                <label>Vehicle Brand:</label>
                                                <select class="vehicle_brand form-control" name="vehicle_brand[{{$j}}]" id="vehicle_brand_{{$j}}">
                                                    @foreach($vehicleBrands as $vehicleBrand)
                                                        <option value="{{$vehicleBrand->id}}" @if($vehicleBrand->id == $array['vehicle_brand_id'])selected @endif)>{{$vehicleBrand->name}}</option>
                                                    @endforeach
                                                </select>

                                                <label>Vehicle Services:</label>
                                                <select class="vehicle_services form-control" name="vehicle_services[{{$j}}][]" id="vehicle_services_{{$j}}" multiple="multiple">
                                                    @foreach($serviceLists as $serviceList)
                                                        @if(in_array($serviceList->id, $array['services']))
                                                            <option value={{$serviceList->id}} selected> {{$serviceList->name}}</option>
                                                        @else
                                                            <option value={{$serviceList->id}}>{{$serviceList->name}}</option>
                                                        @endif
                                                    @endforeach

                                                </select>
                                                @php $j++; @endphp
                                            @endforeach
                                        @else
                                            <label>Vehicle Brand:</label>
                                            <select class="vehicle_brand form-control" name="vehicle_brand[0]" id="vehicle_brand_0">
                                            </select>
                                            
                                            <label>Vehicle Services:</label>
                                            <select class="vehicle_services form-control" name="vehicle_services[0][]" id="vehicle_services_0" multiple="multiple">
                                            </select>
                                        @endif
                                    </div>
                                </div>
                                </div>
                                <div class="add_more_div"></div> 
                                <input type="hidden" name="j_count" id="j_count" value="{{$j}}"> 
                                <a href="javascript:void(0)" class="add_more not_active">Add More</a>
                            <!-- ////////////////////////////// -->

                        </div><!-- panel-body -->

                        <div class="panel-footer">
                            <button type="submit" class="btn btn-primary save">{{ __('voyager::generic.save') }}</button>
                            <a href="{{ route('voyager.'.$dataType->slug.'.index') }}"  class="btn btn-primary">Cancel</a>
                        </div>
                    </form>

                    <iframe id="form_target" name="form_target" style="display:none"></iframe>
                        <form id="my_form" action="{{ route('voyager.upload') }}" target="form_target" method="post"
                                enctype="multipart/form-data" style="width:0;height:0;overflow:hidden">
                            <input name="image" id="upload_file" type="file"
                                     onchange="$('#my_form').submit();this.value='';">
                            <input type="hidden" name="type_slug" id="type_slug" value="{{ $dataType->slug }}">
                            {{ csrf_field() }}
                        </form>

                </div>
            </div>
        </div>
    </div>

    <div class="modal fade modal-danger" id="confirm_delete_modal">
        <div class="modal-dialog">
            <div class="modal-content">

                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"
                            aria-hidden="true">&times;</button>
                    <h4 class="modal-title"><i class="voyager-warning"></i> {{ __('voyager::generic.are_you_sure') }}</h4>
                </div>

                <div class="modal-body">
                    <h4>{{ __('voyager::generic.are_you_sure_delete') }} '<span class="confirm_delete_name"></span>'</h4>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">{{ __('voyager::generic.cancel') }}</button>
                    <button type="button" class="btn btn-danger" id="confirm_delete">{{ __('voyager::generic.delete_confirm') }}</button>
                </div>
            </div>
        </div>
    </div>
    <!-- End Delete File Modal -->
@stop

@section('javascript')
    <script>
        var params = {};
        var $file;

        function deleteHandler(tag, isMulti) {
          return function() {
            $file = $(this).siblings(tag);

            params = {
                slug:   '{{ $dataType->slug }}',
                filename:  $file.data('file-name'),
                id:     $file.data('id'),
                field:  $file.parent().data('field-name'),
                multi: isMulti,
                _token: '{{ csrf_token() }}'
            }

            $('.confirm_delete_name').text(params.filename);
            $('#confirm_delete_modal').modal('show');
          };
        }

        $('document').ready(function () {
           $('.toggleswitch').bootstrapToggle();

            //Init datepicker for date fields if data-datepicker attribute defined
            //or if browser does not handle date inputs
            $('.form-group input[type=date]').each(function (idx, elt) {
                if (elt.type != 'date' || elt.hasAttribute('data-datepicker')) {
                    elt.type = 'text';
                    $(elt).datetimepicker($(elt).data('datepicker'));
                }
            });

            @if ($isModelTranslatable)
                $('.side-body').multilingual({"editing": true});
            @endif

            $('.side-body input[data-slug-origin]').each(function(i, el) {
                $(el).slugify();
            });

            $('.form-group').on('click', '.remove-multi-image', deleteHandler('img', true));
            $('.form-group').on('click', '.remove-single-image', deleteHandler('img', false));
            $('.form-group').on('click', '.remove-multi-file', deleteHandler('a', true));
            $('.form-group').on('click', '.remove-single-file', deleteHandler('a', false));

            $('#confirm_delete').on('click', function(){
                $.post('{{ route('voyager.media.remove') }}', params, function (response) {
                    if ( response
                        && response.data
                        && response.data.status
                        && response.data.status == 200 ) {

                        toastr.success(response.data.message);
                        $file.parent().fadeOut(300, function() { $(this).remove(); })
                    } else {
                        toastr.error("Error removing file.");
                    }
                });

                $('#confirm_delete_modal').modal('hide');
            });
            $('[data-toggle="tooltip"]').tooltip();
        });

        $(document).ready(function(){
            $(".vehicle_type").select2();
            $(".vehicle_brand").select2();
            $(".vehicle_services").select2({
              tags: true
            });

            var j = $("#j_count").val();
            if(j == 0){
                j = 1;
            }
            
            $("#vehicle_type").change(function(){
                var id = $('option:selected', this).val();
                $(".add_more").removeClass("not_active");
                $.ajax({
                    url: "{{ url('/getBrandlist') }}", 
                    type: 'post',
                    data: {
                        _token : '{{ csrf_token() }}',
                        id : id
                    },
                    success: function(response) {
                        console.log(response);
                        $('#vehicle_brand_0')
                        .find('option')
                        .remove()
                        .end();
                        $('#vehicle_brand_0').append($('<option></option>').val("").html(""));
                        $.each(JSON.parse(response), function( key, value ) {
                            $('#vehicle_brand_0').append(new Option(value.name, value.id));
                        });
                    },
                    error: function(jqXHR, textStatus, errorThrown) { // What to do if we fail
                      console.log(JSON.stringify(jqXHR));
                      console.log("AJAX error: " + textStatus + ' : ' + errorThrown);
                    }
                });
            });
            
            
            $(document).on("change",".vehicle_brand",function(){
                var id = $('option:selected', this).val();
                var $this = $(this);
                var typeId = $('option:selected', $(".vehicle_type")).val();
                $.ajax({
                    url: "{{ url('/getServiceList') }}", 
                    type: 'post',
                    data: {
                        _token : '{{ csrf_token() }}',
                        id : id,
                        typeId : typeId,
                    },
                    success: function(response) {
                        //$(".add_more").removeClass("not_active");
                        $this.siblings(".vehicle_services")
                        .find('option')
                        .remove()
                        .end();
                        $this.siblings(".vehicle_services").append($('<option></option>').val("").html(""));
                        $.each(JSON.parse(response), function( key, value ) {
                            $this.siblings(".vehicle_services").append(new Option(value.name, value.id));
                        });
                    },
                    error: function(jqXHR, textStatus, errorThrown) { // What to do if we fail
                      console.log(JSON.stringify(jqXHR));
                      console.log("AJAX error: " + textStatus + ' : ' + errorThrown);
                    }
                });
            });


            $(".add_more").click(function(){
                //$(".add_more").addClass("not_active");
                var brandValues = $("#vehicle_brand_0>option").map(function() { return $(this).val()+"#"+$(this).text(); });
                var html ="";

                html += "<div class='vehicle_main_div'>";
                    html += "<label>Vehicle Brand:</label>";
                    html += "<select class='vehicle_brand form-control' name='vehicle_brand["+j+"]' id='vehicle_brand_"+j+"'>";
                    $.each(brandValues, function( k, v ) {
                        value = v.split("#");
                        html += "<option value='"+value[0]+"'>"+value[1]+"</option>";
                    });
                    html += "</select>";

                    html +="<label>Vehicle Services:</label>";
                    html += "<select class='vehicle_services form-control' name='vehicle_services["+j+"][]' id='vehicle_services_"+j+"' multiple='multiple'>";
                    html += "</select>";
                html +="</div>";

                $(".main_div").append(html);
                $(".vehicle_brand").select2();
                $(".vehicle_services").select2({
                    tags: true
                });
                j++;
            });
        });
    </script>
@stop
