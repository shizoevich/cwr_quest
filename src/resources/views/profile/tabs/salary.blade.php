<div class="">
    <div class="panel-heading">
        <div class="row">
            <div class="col-xs-12 text-center">
                @php
                    $now = \Carbon\Carbon::now();
                @endphp
                <h4>
                    For {{\Carbon\Carbon::now()->format('F Y')}}
                    {{--{{ Form::button('Download',['class'=>'btn pull-right btn-primary'],[]) }}--}}
                </h4>
            </div>
        </div>
    </div>
    <div class="panel-body">
        @php
            $provider = $user->provider;
        @endphp
        @include('dashboard.salary._provider-salary-table')
    </div>
</div>