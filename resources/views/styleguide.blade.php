@extends('layouts.app')

@section('title', 'Styleguide')

@section('content')
    <div class="space-y-card">
        <x-ui.breadcrumb :items="[
            ['label' => 'Student', 'href' => '#'],
            ['label' => 'Ayu Wijaya'],
        ]" />

        <x-ui.page-header title="Ayu Wijaya" subtitle="2201581234">
            <x-slot:media><x-ui.avatar /></x-slot:media>
            <x-slot:actions>
                <x-ui.button variant="secondary" type="button"><x-ui.icon name="pencil" /> Edit</x-ui.button>
                <x-ui.button type="button"><x-ui.icon name="download" /> Download</x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        <x-ui.tabs :tabs="[
            ['label' => 'Overview', 'href' => '#', 'active' => true],
            ['label' => 'Grades', 'href' => '#'],
        ]" />

        <x-ui.card title="Academic Info" icon="school">
            <x-ui.detail-list>
                <x-ui.detail label="Student ID">2201581234</x-ui.detail>
                <x-ui.detail label="Batch">PPTI23</x-ui.detail>
                <x-ui.detail label="Current Semester">Semester 5 (2025 - Odd)</x-ui.detail>
                <x-ui.detail label="Class">1A</x-ui.detail>
                <x-ui.detail label="Cumulative GPA">3.72</x-ui.detail>
                <x-ui.detail label="Academic Status">Active</x-ui.detail>
            </x-ui.detail-list>
        </x-ui.card>

        <x-ui.card title="Grade Point" icon="assignment">
            <x-slot:actions>Average GPA : <strong>3.72</strong></x-slot:actions>
            <x-ui.bar-chart :items="[
                ['label' => '2023 - Odd', 'value' => 3.40],
                ['label' => '2023 - Even', 'value' => 3.55],
                ['label' => '2024 - Odd', 'value' => 3.62],
                ['label' => '2024 - Even', 'value' => 3.68],
            ]" />
        </x-ui.card>

        <x-ui.card title="Buttons" icon="person">
            <div class="space-y-4">
                @foreach (['primary', 'secondary', 'danger', 'ghost'] as $variant)
                    <div class="flex flex-wrap items-center gap-3">
                        <span class="w-24 text-muted">{{ $variant }}</span>
                        <x-ui.button :variant="$variant" size="sm" type="button">Small</x-ui.button>
                        <x-ui.button :variant="$variant" type="button">Medium</x-ui.button>
                        <x-ui.button :variant="$variant" size="lg" type="button">Large</x-ui.button>
                        <x-ui.button :variant="$variant" type="button"><x-ui.icon name="download" /> With icon</x-ui.button>
                        <x-ui.button :variant="$variant" type="button" :disabled="true">Disabled</x-ui.button>
                    </div>
                @endforeach
            </div>
        </x-ui.card>

        <x-ui.card title="Form inputs" icon="pencil">
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-muted">Default</label>
                    <x-form.text-input placeholder="Placeholder" />
                </div>
                <div>
                    <label class="mb-1 block text-muted">Disabled</label>
                    <x-form.text-input value="Disabled value" :disabled="true" />
                </div>
                <div>
                    <label class="mb-1 block text-muted">With error</label>
                    <x-form.text-input value="wrong" />
                    <x-form.input-error :messages="['This field is invalid.']" class="mt-1" />
                </div>
            </div>
        </x-ui.card>

        @php
            $rows = [
                ['PPTI23', '2201581234', 'Ayu Wijaya', 'Active', 'success', '3.72'],
                ['PPTI23', '2201581235', 'Budi Santoso', 'Active', 'success', '3.45'],
                ['PPTI22', '2201581236', 'Citra Dewi', 'Graduated', 'primary', '3.90'],
                ['PPTI24', '2201581237', 'Dimas Prasetyo', 'Active', 'success', '3.10'],
                ['PPTI24', '2201581238', 'Eka Putri', 'Active', 'success', '3.55'],
                ['PPTI23', '2201581239', 'Fajar Nugraha', 'Active', 'success', '3.82'],
                ['PPTI22', '2201581240', 'Gita Savitri', 'Graduated', 'primary', '3.95'],
                ['PPTI23', '2201581241', 'Hendra Gunawan', 'On Leave', 'danger', '3.40'],
                ['PPTI24', '2201581242', 'Indah Permatasari', 'Active', 'success', '3.65'],
                ['PPTI22', '2201581243', 'Joko Wibowo', 'Graduated', 'primary', '3.78'],
            ];
            $columns = ['batch' => 0, 'student_id' => 1, 'name' => 2, 'status' => 3, 'gpa' => 5];
            if (isset($columns[request('sort', '')])) {
                $index = $columns[request('sort', '')];
                usort($rows, fn ($a, $b) => request('direction') === 'desc' ? $b[$index] <=> $a[$index] : $a[$index] <=> $b[$index]);
            }
            $paginator = new \Illuminate\Pagination\LengthAwarePaginator($rows, 1067, 10, 1, ['path' => url()->current()]);
        @endphp

        <x-ui.card title="More form controls" icon="pencil">
            <div class="grid gap-4 sm:grid-cols-2">
                <x-form.field name="sg_note" label="Textarea">
                    <x-form.textarea name="sg_note" placeholder="Write a note..."></x-form.textarea>
                </x-form.field>

                <div class="space-y-4">
                    <div class="flex flex-col gap-2">
                        <span class="text-muted">Checkbox</span>
                        <x-form.checkbox name="sg_a" checked>Checked</x-form.checkbox>
                        <x-form.checkbox name="sg_b">Unchecked</x-form.checkbox>
                        <x-form.checkbox name="sg_c" disabled>Disabled</x-form.checkbox>
                    </div>
                    <div class="flex flex-col gap-2">
                        <span class="text-muted">Radio</span>
                        <x-form.radio name="sg_r" value="1" checked>Option one</x-form.radio>
                        <x-form.radio name="sg_r" value="2">Option two</x-form.radio>
                    </div>
                </div>
            </div>
        </x-ui.card>

        <x-ui.card title="Alerts" icon="assignment">
            <div class="space-y-3">
                <x-ui.alert variant="info" title="Information">Student data was last synced this morning.</x-ui.alert>
                <x-ui.alert variant="success" title="Saved">The student record was updated.</x-ui.alert>
                <x-ui.alert variant="warning" title="Check this">Some grades are still missing.</x-ui.alert>
                <x-ui.alert variant="danger" title="Something went wrong">The file could not be generated.</x-ui.alert>
            </div>
        </x-ui.card>

        <x-ui.card title="Icon inputs, toasts and loading" icon="mail">
            <div class="space-y-6">
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-form.field name="sg_email" label="Email">
                        <x-form.icon-input icon="mail" type="email" name="sg_email" placeholder="name@binus.edu" />
                    </x-form.field>
                    <x-form.field name="sg_password" label="Password">
                        <x-form.password-input name="sg_password" placeholder="Enter password" />
                    </x-form.field>
                </div>

                <div class="space-y-3">
                    <x-ui.toast variant="success">The student record was saved.</x-ui.toast>
                    <x-ui.toast variant="danger" title="Could not save">Please check the form and try again.</x-ui.toast>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <x-ui.button type="button" :loading="true">Saving...</x-ui.button>
                    <x-ui.button type="button" variant="secondary" :loading="true">Loading</x-ui.button>
                    <x-ui.button type="button" variant="danger" :loading="true">Deleting...</x-ui.button>
                </div>
            </div>
        </x-ui.card>

        <x-ui.card title="Links, badges and date filters" icon="history">
            <div class="space-y-6">
                <div class="flex flex-wrap items-center gap-3">
                    <x-form.date-range name="date" class="max-w-xs" />
                    <x-form.select class="w-44"><option>All Admins</option></x-form.select>
                    <x-form.select class="w-44"><option>All Actions</option></x-form.select>
                    <x-ui.button type="button" variant="secondary"><x-ui.icon name="filter_alt_off" /> Reset</x-ui.button>
                </div>

                <x-ui.table :headings="['Action', 'Target Entity', 'Lecturer']">
                    <tr>
                        <td><x-ui.badge variant="neutral">Update</x-ui.badge></td>
                        <td><x-ui.link href="#">CourseAssignment</x-ui.link></td>
                        <td><x-ui.link href="#">Dr. Hendra Wijaya</x-ui.link></td>
                    </tr>
                    <tr>
                        <td><x-ui.badge variant="neutral">Create</x-ui.badge></td>
                        <td><x-ui.link href="#">Student</x-ui.link></td>
                        <td><span class="text-muted">&mdash;</span></td>
                    </tr>
                </x-ui.table>
            </div>
        </x-ui.card>

        <x-ui.card title="Modal and empty state" icon="school">
            <div class="space-y-6">
                <x-ui.button type="button" data-modal-open="#demo-modal">Open modal</x-ui.button>
                <x-ui.button type="button" variant="secondary" data-modal-open="#demo-confirm">Open confirm</x-ui.button>

                <x-ui.empty-state title="No students found" description="Try changing the search or filters." icon="search">
                    <x-ui.button type="button" variant="secondary">Clear filters</x-ui.button>
                </x-ui.empty-state>
            </div>
        </x-ui.card>

        <x-ui.confirm id="demo-confirm" />

        <x-ui.modal id="demo-modal" title="Delete student?">
            <p>This will permanently remove the student record. This action cannot be undone.</p>

            <x-slot:footer>
                <x-ui.button type="button" variant="secondary" data-modal-close>Cancel</x-ui.button>
                <x-ui.button type="button" variant="danger" data-modal-close>Delete</x-ui.button>
            </x-slot:footer>
        </x-ui.modal>

        <h2 class="pt-8 text-title">List page pattern</h2>

        <x-ui.page-header title="Student List" divider>
            <x-slot:actions>
                <x-ui.button type="button"><x-ui.icon name="download" /> Download</x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        <div class="flex flex-wrap items-center gap-x-10 gap-y-2">
            <x-ui.stat label="Total" value="1067" />
            <x-ui.stat label="Active" value="167" color="success" />
            <x-ui.stat label="Graduated" value="567" color="primary" />
            <x-ui.stat label="On Leave" value="67" color="danger" />
        </div>

        <div class="flex gap-4">
            <x-form.search-input class="flex-1" aria-label="Search students" />
            <x-ui.button type="button" variant="secondary" data-toggle="#student-filters" aria-expanded="true" aria-controls="student-filters">
                <x-ui.icon name="filter_alt" /> Filter
            </x-ui.button>
        </div>

        <div id="student-filters" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <x-form.label for="f-batch">Batch</x-form.label>
                <x-form.select id="f-batch"><option>All</option><option>PPTI22</option><option>PPTI23</option><option>PPTI24</option></x-form.select>
            </div>
            <div>
                <x-form.label for="f-status">Status</x-form.label>
                <x-form.select id="f-status"><option>All</option><option>Active</option><option>Graduated</option><option>On Leave</option></x-form.select>
            </div>
            <div>
                <x-form.label for="f-class">Class</x-form.label>
                <x-form.select id="f-class"><option>All</option><option>1A</option><option>1B</option></x-form.select>
            </div>
            <div>
                <x-form.label>GPA Range</x-form.label>
                <x-form.range-input name="gpa" />
            </div>
        </div>

        <x-ui.table :headings="['Batch', 'Student ID', 'Name', 'Status', 'GPA']">
            @foreach ($rows as [$batch, $nim, $name, $status, $color, $gpa])
                <tr>
                    <td>{{ $batch }}</td>
                    <td>{{ $nim }}</td>
                    <td>{{ $name }}</td>
                    <td><x-ui.badge :color="$color">{{ $status }}</x-ui.badge></td>
                    <td>{{ $gpa }}</td>
                </tr>
            @endforeach

            <x-slot:footer>
                <x-ui.pagination :paginator="$paginator" />
            </x-slot:footer>
        </x-ui.table>
    </div>
@endsection
