@props(['name'])

{{-- Displays error messages from Alpine errors object --}}
<template x-if="errors?.{{ $name }}">
    <div class="mt-1 space-y-0.5">
        <template x-for="msg in errors.{{ $name }}" :key="msg">
            <p class="text-xs text-danger-600" x-text="msg"></p>
        </template>
    </div>
</template>
