@extends('master')

@section('title', 'Page Title')

@section('content')
    <p>This is my body content.</p>
    <p>{{$data->url}}</p>
@stop