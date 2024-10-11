@extends('layouts.app')

@section('content')
    @include('modals.update-notifications.preview')

    <div class="wrapper">
        <div class="container">
            <div class="row">
                <div class="col-md-8 col-md-offset-2">
                    <h3>{{ isset($template) ? 'Edit' : 'Create' }} Template</h3>
                    <div class="panel panel-default">
                        <div class="panel-body" style="position:relative;">
                            <div class="preloader">
                                <img src="/images/pageloader.gif" alt="">
                            </div>

                            <form
                                id="template-form"
                                role="form"
                                method="POST"
                                action="{{ isset($template) ? route('update-notification-templates.update', $template->id) : route('update-notification-templates.store') }}"
                            >
                                {{ csrf_field() }}

                                @isset($template)
                                    {{ method_field('PUT') }}
                                @endisset

                                <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                                    <label for="name" class="control-label required">Name</label>
                                    <input
                                        id="name"
                                        name="name"
                                        type="text"
                                        class="form-control"
                                        value="{{ old('name', isset($template) ? $template->name : '') }}"
                                    >
                                    <span class="help-block with-errors">
                                    @if ($errors->has('name'))
                                        <strong>{{ $errors->first('name') }}</strong>
                                    @endif
                                    </span>
                                </div>

                                <div class="form-group {{ $errors->has('notification_title') ? 'has-error' : '' }}">
                                    <label for="notification_title" class="control-label required">Title</label>
                                    <input
                                        id="notification_title"
                                        name="notification_title"
                                        type="text"
                                        class="form-control"
                                        value="{{ old('notification_title', isset($template) ? $template->notification_title : '') }}"
                                    >
                                    <span class="help-block with-errors">
                                    @if ($errors->has('notification_title'))
                                        <strong>{{ $errors->first('notification_title') }}</strong>
                                    @endif
                                    </span>
                                </div>

                                <div class="form-group {{ $errors->has('notification_content') ? 'has-error' : '' }}">
                                    <label for="tinymce-editor" class="control-label required">Content</label>
                                    <textarea id="tinymce-editor" name="notification_content" class="form-control" style="height:200px;resize:vertical;">{{ old('notification_content', isset($template) ? $template->notification_content : '') }}</textarea>
                                    <span class="help-block with-errors">
                                    @if ($errors->has('notification_content'))
                                        <strong>{{ $errors->first('notification_content') }}</strong>
                                    @endif
                                    </span>
                                </div>

                                <div class="form-group">
                                    <button type="button" id="cancel-btn" class="btn btn-secondary pull-right" style="margin-left:10px;">
                                        Cancel
                                    </button>
                                    <button type="button" id="preview-btn" class="btn btn-primary pull-right">
                                        Preview
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @parent
    <script src="{{ asset('js/tinymce/tinymce.min.js') }}" referrerpolicy="origin"></script>
    <script src="{{ asset('js/update-notification-templates/form.js') }}"></script>
@endsection