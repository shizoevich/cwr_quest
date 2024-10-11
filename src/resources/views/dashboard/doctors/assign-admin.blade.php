@extends('layouts.app')

@section('content')
    <div class="wrapper">
        <div class="container">
            <div class="row">
                <div class="col-sm-6 col-sm-offset-3">
                    @if(Session::has('message'))
                        <div class="alert alert-success">
                            {{Session::get('message')}}
                        </div>
                    @endif
                    <div class="panel panel-default">
                        <div class="panel-body">
                            <form method="post">
                                {{csrf_field()}}
                                <div class="form-group{{ $errors->has('provider_id') ? ' has-error' : '' }}">
                                    <label>Assign To</label>
                                    <select name="provider_id" class="form-control">
                                        <option value="-1" selected disabled></option>
                                        @foreach($providers as $provider)
                                            <option value="{{$provider->id}}" @if($provider->id == $user->provider_id){{"selected"}}@endif>
                                                {{$provider->provider_name}}
                                            </option>
                                        @endforeach
                                    </select>
                                    <span class="help-block with-errors">
                                        @if ($errors->has('provider_id'))
                                            <strong>{{ $errors->first('provider_id') }}</strong>
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
    </div>
@endsection