@extends('layouts.app')

@section('content')
    <div class="wrapper" id="salary-wrapper">
        <div class="container">
            @if (session('message'))
                <div class="row">
                    <div class="col-xs-12">
                        <div class="alert alert-success alert-dismissible">
                            <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                            {!! session('message') !!}
                        </div>
                    </div>

                </div>
            @endif
            <div class="row">
                <div class="col-xs-12">
                    <div class="table-responsive">


                        <form action="{{ route('system.queue-jobs.delete') }}" method="post">
                            {{ csrf_field() }}
                            {{ method_field('delete') }}
                            <input type="hidden" name="password" value="{{ \Request::get('password') }}">
                            <input type="submit" class="btn btn-danger" value="Delete Job(s)"/>
                            <table class="table table-bordered table-condensed table-striped">
                            <thead>
                            <tr>
                                <th></th>
                                <th>Name</th>
                                <th>Attempts</th>
                                <th>Reserved At</th>
                                <th>Available At</th>
                                <th>Created At</th>
                                <th>Payload</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($jobs as $item)
                                <tr>
                                    <td><input type="checkbox" name="jobs[]" value="{{ $item->id }}"></td>
                                    <td>{{ $item->queue }}</td>
                                    <td>{{ $item->attempts }}</td>
                                    <td>{{ is_null($item->reserved_at) ? '-' : \Carbon\Carbon::createFromTimestamp($item->reserved_at)->setTimezone('+03:00')->toDateTimeString() }}</td>
                                    <td>{{ \Carbon\Carbon::createFromTimestamp($item->available_at)->setTimezone('+03:00')->toDateTimeString() }}</td>
                                    <td>{{ \Carbon\Carbon::createFromTimestamp($item->created_at)->setTimezone('+03:00')->toDateTimeString() }}</td>
                                    <td>{{ $item->payload }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @parent
@endsection