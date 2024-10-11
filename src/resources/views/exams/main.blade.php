@extends('layouts.app')

@section('content')
    <div class="wrapper">
        <div class="container">
            <div class="row">

                <div class="msform">
                    <!-- multistep form -->
                    <form id="msform" name="msform" class="form-horizontal" method="post" action="">
                        {{ csrf_field() }}

                        <input type="hidden" name="training" value="{{ $training_id }}">

                        <!-- progressbar -->
                        <ul id="progressbar" class="bg-gray box-body">
                            <li class="active"></li>
                            @for($i=0;$i<(count($questions)-1);$i++)
                                <li></li>
                            @endfor
                        </ul>

                        <div class="timer">
                            <svg width="120" height="120">
                                <circle transform="rotate(-90)" r="50" cx="-60" cy="60"/>
                                <circle class="time-circle" transform="rotate(-90)" r="50" cx="-60" cy="60"/>
                            </svg>
                            <div class="timer-num">
                                <h1 id="time"></h1>
                            </div>
                        </div>

                        @for($i=0;$i<count($questions);$i++)
                            <fieldset>
                                <h2 class="fs-title">{{ $questions[$i]["question"]  }}</h2>
                                <div class="form-group">
                                    @for($j=0;$j<(count($questions[$i]["answers"]));$j++)
                                        <div class="radio">
                                            <label for="answer-{{ $i }}-{{ $j }}">
                                                <input type="radio" name="answers[{{ $questions[$i]["id"] }}]"
                                                       id="answer-{{ $i }}-{{ $j }}"
                                                       value="{{ $questions[$i]["answers"][$j]["id"] }}">
                                                {{ $questions[$i]["answers"][$j]["answer"] }}
                                            </label>
                                        </div>
                                    @endfor
                                </div>
                                @if($i==0)
                                    <input type="button" name="next" class="next action-button" value="Next" disabled/>
                                @elseif($i==(count($questions)-1))
                                    <input type="button" class="previous action-button" value="Previous"/>
                                    <input type="submit" class="submit action-button" value="Submit" disabled/>
                                @else
                                    <input type="button" class="previous action-button" value="Previous"/>
                                    <input type="button" class="next action-button" value="Next" disabled/>
                                @endif
                            </fieldset>
                        @endfor

                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection