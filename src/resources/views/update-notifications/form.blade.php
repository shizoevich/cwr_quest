@extends('layouts.app')

@section('content')
    @include('modals.update-notifications.preview')

    <div class="wrapper">
        <div class="container">
            <div class="row">
                <div class="col-md-8 col-md-offset-2">
                    <h3>{{ isset($notification) ? 'Edit' : 'Create' }} Notification</h3>
                    <div class="panel panel-default">
                        <div class="panel-body" style="position:relative;">
                            <div class="preloader">
                                <img src="/images/pageloader.gif" alt="">
                            </div>

                            <form
                                id="notification-form"
                                role="form"
                                method="POST"
                                action="{{ isset($notification) ? route('update-notifications.update', $notification->id) : route('update-notifications.store') }}"
                            >
                                {{ csrf_field() }}

                                @isset($notification)
                                    {{ method_field('PUT') }}
                                @endisset

                                <div class="form-group {{ $errors->has('show_date') ? 'has-error' : '' }}">
                                    <label for="show_date" class="control-label">Date</label>

                                    @php
                                        $showDate = '';
                                        if (old('show_date')) {
                                            $showDate = old('show_date');
                                        } elseif (isset($notification)) {
                                            $showDate = \Carbon\Carbon::parse($notification->show_date)->format('Y-m-d H:i:00');
                                        }
                                    @endphp

                                    <input
                                        id="show_date"
                                        name="show_date"
                                        type="datetime-local"
                                        class="form-control"
                                        value="{{ $showDate }}"
                                    >
                                    <span class="help-block with-errors">
                                    @if ($errors->has('show_date'))
                                        <strong>{{ $errors->first('show_date') }}</strong>
                                    @endif
                                    </span>
                                </div>

                                @if (!isset($user))
                                    <div class="form-group {{ $errors->has('user_ids') ? 'has-error' : '' }}">
                                        <label for="user_ids" class="control-label required">Users</label>
                                        <div class="users-select-actions">
                                            <div class="users-select-btn-wrap">
                                                <button type="button" class="users-select-btn" id="select-all-providers">Select all providers</button>
                                                <span class="users-select-count">(0)</span>
                                            </div>
                                            <div class="users-select-btn-wrap">
                                                <button type="button" class="users-select-btn" id="select-all-secretaries">Select all secretaries</button>
                                                <span class="users-select-count">(0)</span>
                                            </div>
                                            <div class="users-select-btn-wrap">
                                                <button type="button" class="users-select-btn" id="unselect-all">Unselect all</button>
                                            </div>
                                        </div>
                                        <select id="user_ids" name="user_ids[]" class="form-control" multiple="multiple" style="height:36px;">
                                            @foreach($users as $user)
                                                <option
                                                    value="{{ $user->id }}"
                                                    @if ($user->isSecretary())
                                                        data-role="secretary"
                                                    @elseif ($user->isProvider())
                                                        data-role="provider"
                                                    @endif
                                                    @if (old('user_ids'))
                                                        @if (in_array($user->id, old('user_ids')))
                                                            {{ 'selected' }}
                                                        @endif
                                                    @elseif (isset($notification) && isset($notification->users) && $notification->users->contains('id', $user->id))
                                                        {{ 'selected' }}
                                                    @endif
                                                >{{ isset($user->name) ? $user->name . ' (' . $user->email . ')' : $user->email }}</option>
                                            @endforeach
                                        </select>
                                        <span class="help-block with-errors">
                                        @if ($errors->has('user_ids'))
                                            <strong>{{ $errors->first('user_ids') }}</strong>
                                        @endif
                                        </span>
                                    </div>
                                    <ul class="users-list hidden"></ul>
                                @else
                                    <input type="hidden" name="user_ids[]" value="{{ $user->id }}">
                                @endif

                                @if (!isset($template))
                                    <div class="form-group">
                                        <label for="template" class="control-label">Template</label>
                                        <select id="template" name="template" class="form-control" style="height:36px;">
                                            <option value="none" selected disabled>None selected</option>
                                            @foreach($templates as $t)
                                                <option value="{{ $t->id }}">
                                                    {{ $t->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <span class="help-block with-errors"></span>
                                    </div>
                                @endif

                                <div class="form-group {{ $errors->has('title') ? 'has-error' : '' }}">
                                    <label for="title" class="control-label required">Title</label>

                                    @php
                                        $title = '';
                                        if (old('title')) {
                                            $title = old('title');
                                        } elseif (isset($template)) {
                                            $title = $template->notification_title ?? '';
                                        } elseif (isset($notification)) {
                                            $title = $notification->title ?? '';
                                        }
                                    @endphp

                                    <input
                                        id="title"
                                        name="title"
                                        type="text"
                                        class="form-control"
                                        value="{{ $title }}"
                                    >
                                    <span class="help-block with-errors">
                                    @if ($errors->has('title'))
                                        <strong>{{ $errors->first('title') }}</strong>
                                    @endif
                                    </span>
                                </div>

                                <div class="form-group {{ $errors->has('content') ? 'has-error' : '' }}">
                                    <label for="tinymce-editor" class="control-label required">Content</label>

                                    @php
                                        $content = '';
                                        if (old('content')) {
                                            $content = old('content');
                                        } elseif (isset($template)) {
                                            $content = $template->notification_content ?? '';
                                        } elseif (isset($notification)) {
                                            $content = $notification->content ?? '';
                                        }
                                    @endphp
                                    
                                    <textarea id="tinymce-editor" name="content" class="form-control" style="height:200px;resize:vertical;">{{ $content }}</textarea>
                                    <span class="help-block with-errors">
                                    @if ($errors->has('content'))
                                        <strong>{{ $errors->first('content') }}</strong>
                                    @endif
                                    </span>
                                </div>

                                <div class="form-group">
                                    <label style="padding-top:15px;">
                                        <input
                                            type="checkbox"
                                            name="is_required"
                                            value="1"
                                            @if (old('is_required'))
                                                {{ 'checked' }}
                                            @elseif (isset($notification) && $notification->is_required)
                                                {{ 'checked' }}
                                            @endif
                                        >
                                        Viewing required
                                    </label>
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
    <script src="{{ asset('js/update-notifications/form.js') }}"></script>
@endsection