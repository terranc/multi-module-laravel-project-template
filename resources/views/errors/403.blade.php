@extends('errors::minimal')

@section('title', __('权限不足'))
@section('code', '403')
@section('message', __($exception->getMessage() ?: '权限不足'))
