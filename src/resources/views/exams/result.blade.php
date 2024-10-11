@extends('layouts.app')

@section('content')

    <div class="wrapper">
        <div class="container">
            <div class="row">
                <div class="msform-result">

                    <h2>Correct Answers: {{$result}} out of 14</h2>

                    @if ($result > 10)
                        <h3>Congratulations! You passed the exam!</h3>
                    @else
                        <h3>Unfortunately you did not pass the exam...</h3>
                    @endif

                    <div class="text-center">
                        <a class="btn btn-primary" href="{{route('training')}}?tab=hippa">
                            Back
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop