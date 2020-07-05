@extends('errors::minimal')

@section('title', __('服务不存在'))
@section('code', '503')
@section('message', __($exception->getMessage() ?: '服务不存在'))
