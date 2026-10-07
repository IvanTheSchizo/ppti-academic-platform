@extends('layouts.app')

@section('title', 'Lecturer Profile')

@section('content')
    <div class="space-y-card">
        {{-- Breadcrumb --}}
        <x-ui.breadcrumb :items="[
            ['label' => 'Lecturer', 'href' => route('lecturers.index')],
            ['label' => $lecturer->name],
        ]" />

        {{-- Page Header dengan Avatar dan Tombol Aksi --}}
        <x-ui.page-header :title="$lecturer->name" :subtitle="$lecturer->lecturer_code">
            <x-slot:media><x-ui.avatar /></x-slot:media>
            <x-slot:actions>
                 <x-ui.button variant="secondary" type="button" data-modal-open="#edit-lecturer-modal">
                    <x-ui.icon name="pencil" /> Edit
                 </x-ui.button>
                <x-ui.button type="button"><x-ui.icon name="download" /> Download</x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        {{-- Navigasi Tab (Overview & Courses) --}}
        <x-ui.tabs :tabs="[
            ['label' => 'Overview', 'href' => request()->fullUrlWithQuery(['tab' => 'overview']), 'active' => $activeTab === 'overview'],
            ['label' => 'Courses', 'href' => request()->fullUrlWithQuery(['tab' => 'courses']), 'active' => $activeTab === 'courses'],
        ]" />

        {{-- Konten Berdasarkan Tab yang Aktif --}}
        @if ($activeTab === 'courses')
            @include('lecturers.partials.profile-courses')
        @else
            @include('lecturers.partials.profile-overview')
        @endif
    </div>

    {{-- Modal Edit Lecturer (Dilebarkan dengan max-w-2xl atau max-w-3xl) --}}
    <x-ui.modal id="edit-lecturer-modal" title="Edit Lecturer" class="max-w-2xl">
        <form id="edit-lecturer-form" method="POST" action="{{ route('api.lecturers.update', $lecturer->id) }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <x-form.label for="lecturer_code">Lecturer Code</x-form.label>
                    <x-form.text-input id="lecturer_code" name="lecturer_code" :value="old('lecturer_code', $lecturer->lecturer_code)" required />
                </div>
                <div>
                    <x-form.label for="name">Full Name</x-form.label>
                    <x-form.text-input id="name" name="name" :value="old('name', $lecturer->name)" required />
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <x-form.label for="latest_education">Latest Education</x-form.label>
                    <x-form.text-input id="latest_education" name="latest_education" :value="old('latest_education', $lecturer->latest_education)" />
                </div>
                <div>
                    <x-form.label for="jja">Academic Functional Rank</x-form.label>
                    <x-form.text-input id="jja" name="jja" :value="old('jja', $lecturer->jja)" />
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <x-form.label for="status">Status</x-form.label>
                    <x-form.select id="status" name="status">
                        <option value="Active" @selected($lecturer->status === 'Active')>Active</option>
                        <option value="Inactive" @selected($lecturer->status === 'Inactive')>Inactive</option>
                        <option value="On Leave" @selected($lecturer->status === 'On Leave')>On Leave</option>
                    </x-form.select>
                </div>
                <div>
                    <x-form.label for="gender">Gender</x-form.label>
                    <x-form.select id="gender" name="gender">
                        <option value="Male" @selected(($lecturer->gender ?? 'Male') === 'Male')>Male</option>
                        <option value="Female" @selected(($lecturer->gender ?? '') === 'Female')>Female</option>
                    </x-form.select>
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <x-form.label for="email_binus_edu">Email 1</x-form.label>
                    <x-form.text-input type="email" id="email_binus_edu" name="email_binus_edu" :value="old('email_binus_edu', $lecturer->email_binus_edu)" />
                </div>
                <div>
                    <x-form.label for="email_binus_ac_id">Email 2</x-form.label>
                    <x-form.text-input type="email" id="email_binus_ac_id" name="email_binus_ac_id" :value="old('email_binus_ac_id', $lecturer->email_binus_ac_id)" />
                </div>
            </div>

            <div>
                <x-form.label for="phone_number">Phone Number</x-form.label>
                <x-form.text-input id="phone_number" name="phone_number" :value="old('phone_number', $lecturer->phone_number)" />
            </div>
        </form>

        <x-slot:footer>
            <x-ui.button type="button" variant="secondary" data-modal-close>Cancel</x-ui.button>
            <x-ui.button type="submit" form="edit-lecturer-form">Save Changes</x-ui.button>
        </x-slot:footer>
    </x-ui.modal>
@endsection