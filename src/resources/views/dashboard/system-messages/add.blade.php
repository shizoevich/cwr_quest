@extends('layouts.app')

@section('content')
    <div class="wrapper">
        <div class="container">
            <div class="row">
                <div class="panel panel-default">
                    <div class="panel-body">
                        <form method="post"
                              action="@if(!isset($message)){{route('system-messages.add')}}@else{{route('system-messages.edit', ['id' => $message->id])}}@endif">
                            {{csrf_field()}}
                            <div class="form-group">
                                <label>Popup size</label>
                                <select class="form-control" name="modal_class">
                                    <option value="">Default</option>
                                    <option value="modal-sm" @if(isset($message) && $message->modal_class == 'modal-sm'){{'selected'}}@endif>Small</option>
                                    <option value="modal-lg" @if(isset($message) && $message->modal_class == 'modal-lg'){{'selected'}}@endif>Large</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>
                                    <input type="checkbox" value="true" name="only_for_admin" @if(isset($message) && $message->only_for_admin){{'checked'}}@endif>
                                    Visible Only For Admin
                                </label>
                            </div>
                            <div class="form-group{{ $errors->has('title') ? ' has-error' : '' }}">
                                <label>Title</label>
                                <input type="text" class="form-control" name="title" maxlength="255" value="{{$message->title or ""}}">
                                <span class="help-block with-errors">
                                    @if ($errors->has('title'))
                                        <strong>{{ $errors->first('title') }}</strong>
                                    @endif
                                </span>
                            </div>
                            <div class="form-group{{ $errors->has('text') ? ' has-error' : '' }}">
                                @if(isset($message) && $message->id)
                                <input type="hidden" name="id" value="{{$message->id}}">
                                @endif
                                <textarea name="text" id="system-message-textarea" rows="0" cols="0">{{$message->text or ""}}</textarea>
                                <span class="help-block with-errors">
                                    @if ($errors->has('text'))
                                        <strong>{{ $errors->first('text') }}</strong>
                                    @endif
                                </span>
                            </div>

                            <button type="submit" class="btn btn-primary pull-right">Submit</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @parent
    <script src="https://cloud.tinymce.com/stable/tinymce.min.js"></script>
    <script>
        $(document).ready(function() {
            tinymce.init({
                selector: '#system-message-textarea',
                plugins: "link lists textcolor image media",
                height: 500,
                menu: {},
                toolbar: 'formatselect fontsizeselect | alignleft aligncenter alignright alignjustify | bold italic underline strikethrough | forecolor | numlist bullist | link unlink | image | media',
            })
        });
    </script>
@endsection