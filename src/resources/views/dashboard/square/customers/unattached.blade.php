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
            <div class="panel panel-default">
                <div class="panel-body">
                    <form>
                        <div class="input-group">
                            <input type="text" class="form-control" placeholder="Search" name="q"
                                   value="{{\Request::get('q')}}">
                            <span class="input-group-btn">
                                <button class="btn btn-primary" type="submit">Search</button>
                            </span>
                        </div><!-- /input-group -->
                    </form>

                    @if($accounts->isEmpty())
                        <div class="alert alert-info" style="margin-top:37px;">
                            @if(empty(\Request::get('q')))
                                All Square customers have been attached to the patients.
                            @else
                                No search results.
                            @endif
                        </div>
                    @endif

                    @foreach($accounts as $account)
                        <div>
                            <div class="row">
                                <div class="col-sm-9">
                                    <h2>
                                        {{title_case($account->first_name . ' ' . $account->last_name)}}
                                        (<a target="_blank"
                                            href="https://squareup.com/dashboard/customers/directory/customer/{{$account->external_id}}">Square</a>)
                                    </h2>
                                </div>
                                <div class="col-sm-3">
                                    <attach-customer-to-patient class="pull-right"
                                                                csrf-token="{{csrf_token()}}"
                                                                customer-name="{{title_case($account->first_name . ' ' . $account->last_name)}}"
                                                                :customer-id="{{ $account->id }}"></attach-customer-to-patient>
                                </div>
                            </div>

                            @if($account->transactions->isNotEmpty())
                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered table-condensed unattached-customers-table">
                                        <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Payment Method</th>
                                            <th>Payment Info</th>
                                            <th>Amount</th>
                                            <th>Square Link</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($account->transactions as $transaction)
                                            <tr>
                                                <td>
                                                    {{ $transaction->transaction_date->format('m/d/Y h:i A') }}
                                                </td>
                                                <td>{{title_case($transaction->method)}}</td>
                                                <td>
                                                    {{title_case($transaction->card_brand)}}
                                                    <br>
                                                    @if(null !== $transaction->card_last_four)
                                                        **** **** **** {{$transaction->card_last_four}}
                                                    @endif
                                                </td>
                                                <td>${{$transaction->amount_money / 100}}</td>
                                                <td>
                                                    <a target="_blank"
                                                       href="https://squareup.com/dashboard/sales/transactions/{{ $transaction->external_id }}">
                                                        Square
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <h5 class="text-center">No Payments</h5>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection