@extends('layouts.app')

@section('title', 'Student List')

@section('content')

    <div class="space-y-6">

        <x-ui.page-header title="Student List" divider>

            <x-slot:actions>

                <x-ui.button
                    type="button"
                    onclick="toggleEditMode()"
                    color="secondary"
                >
                    <x-ui.icon name="pencil" />
                    Edit List
                </x-ui.button>

                <x-ui.button
                    type="button"
                    onclick="openAddModal()"
                >
                    <x-ui.icon name="person" />
                    Add Student
                </x-ui.button>

                <a href="{{ route('students.export') }}">
                    <x-ui.button type="button">
                        <x-ui.icon name="download" />
                        Download
                    </x-ui.button>
                </a>

            </x-slot:actions>

        </x-ui.page-header>


        {{-- Existing teammate components --}}
        @include('students.partials.stats')

        @include('students.partials.filters')

        @include('students.partials.table')

    </div>


    {{-- =========================================================
         STUDENT ADD / EDIT MODAL
         ========================================================= --}}

    <div
        id="studentModal"
        class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-sm"
    >

        <div class="w-full max-w-lg p-6 bg-white rounded-lg shadow-xl m-4">

            <div class="flex items-center justify-between mb-6">

                <h2
                    id="modalTitle"
                    class="text-xl font-bold text-gray-900"
                >
                    Add New Student
                </h2>

                <button
                    type="button"
                    onclick="closeStudentModal()"
                    class="text-gray-500 hover:text-gray-900 text-xl"
                >
                    &times;
                </button>

            </div>


            {{-- Error message --}}
            <div
                id="formErrors"
                class="hidden mb-4 rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-700"
            ></div>


            <form
                id="studentForm"
                onsubmit="submitStudentForm(event)"
            >

                <div class="space-y-4">


                    {{-- Student ID --}}
                    <div>

                        <label class="block text-sm font-medium mb-1 text-gray-700">
                            Student ID
                        </label>

                        <x-form.text-input
                            id="nimInput"
                            name="nim"
                            required
                        />

                    </div>


                    {{-- Name --}}
                    <div>

                        <label class="block text-sm font-medium mb-1 text-gray-700">
                            Full Name
                        </label>

                        <x-form.text-input
                            id="nameInput"
                            name="name"
                            required
                        />

                    </div>


                    {{-- Batch --}}
                    <div>

                        <label class="block text-sm font-medium mb-1 text-gray-700">
                            Batch
                        </label>

                        <x-form.select
                            id="batchInput"
                            name="batch_id"
                            required
                            onchange="updateClassOptions()"
                        >

                            <option value="">
                                Select batch
                            </option>

                            @foreach ($batches as $batch)

                                <option value="{{ $batch->id }}">
                                    {{ $batch->batch_name }}
                                </option>

                            @endforeach

                        </x-form.select>

                    </div>



                    {{-- GPA --}}
                    <div>

                        <label class="block text-sm font-medium mb-1 text-gray-700">
                            GPA
                        </label>

                        <x-form.text-input
                            id="gpaInput"
                            name="cumulative_gpa"
                            type="number"
                            min="0"
                            max="4"
                            step="0.01"
                        />

                    </div>


                    {{-- Status --}}
                    <div>

                        <label class="block text-sm font-medium mb-1 text-gray-700">
                            Status
                        </label>

                        <x-form.select
                            id="statusInput"
                            name="status"
                            required
                        >

                            <option value="Active">
                                Active
                            </option>

                            <option value="Graduated">
                                Graduated
                            </option>

                            <option value="On Leave">
                                On Leave
                            </option>

                        </x-form.select>

                    </div>


                    {{-- Buttons --}}
                    <div class="flex justify-end gap-3 mt-8">

                        <x-ui.button
                            type="button"
                            onclick="closeStudentModal()"
                            color="muted"
                        >
                            Cancel
                        </x-ui.button>

                        <x-ui.button
                            id="saveStudentButton"
                            type="submit"
                            color="primary"
                        >
                            Save Student
                        </x-ui.button>

                    </div>

                </div>

            </form>

        </div>

    </div>


    {{-- =========================================================
         JAVASCRIPT
         ========================================================= --}}

    <script>

        const modal =
            document.getElementById('studentModal');

        const modalTitle =
            document.getElementById('modalTitle');

        const studentForm =
            document.getElementById('studentForm');

        const nimInput =
            document.getElementById('nimInput');

        const nameInput =
            document.getElementById('nameInput');

        const batchInput =
            document.getElementById('batchInput');

        const gpaInput =
            document.getElementById('gpaInput');

        const statusInput =
            document.getElementById('statusInput');

        const formErrors =
            document.getElementById('formErrors');

        const saveStudentButton =
            document.getElementById('saveStudentButton');


        /*
         * Class data supplied by Laravel.
         *
         * This lets us filter classes based on
         * the selected batch.
         */



        /*
         * null = Add mode
         *
         * number = Edit mode
         */
        let editingStudentId = null;

        let editMode = false;


        /*
         * =========================================================
         * ERROR HANDLING
         * =========================================================
         */

        function clearFormErrors()
        {
            formErrors.classList.add('hidden');

            formErrors.innerHTML = '';
        }


        function showFormErrors(message)
        {
            formErrors.innerHTML = message;

            formErrors.classList.remove('hidden');
        }




        /*
         * =========================================================
         * ADD STUDENT
         * =========================================================
         */

        function openAddModal()
        {
            editingStudentId = null;

            modalTitle.innerText =
                'Add New Student';

            saveStudentButton.innerText =
                'Save Student';


            studentForm.reset();

            clearFormErrors();



            modal.classList.remove('hidden');

            modal.classList.add('flex');

            nimInput.focus();
        }


        /*
         * =========================================================
         * EDIT STUDENT
         * =========================================================
         */

        function openEditModal(student)
        {
            editingStudentId =
                student.id;


            modalTitle.innerText =
                'Edit Student';

            saveStudentButton.innerText =
                'Save Changes';


            clearFormErrors();


            nimInput.value =
                student.nim ?? '';

            nameInput.value =
                student.name ?? '';

            batchInput.value =
                student.batch_id ?? '';

            gpaInput.value =
                student.cumulative_gpa ?? '';

            statusInput.value =
                student.status ?? 'Active';


            modal.classList.remove('hidden');

            modal.classList.add('flex');

            nameInput.focus();
        }


        /*
         * =========================================================
         * CLOSE
         * =========================================================
         */

        function closeStudentModal()
        {
            modal.classList.remove('flex');

            modal.classList.add('hidden');

            clearFormErrors();
        }


        /*
         * =========================================================
         * SUBMIT
         * =========================================================
         */

        async function submitStudentForm(event)
        {
            event.preventDefault();

            clearFormErrors();


            saveStudentButton.disabled =
                true;


            saveStudentButton.innerText =
                editingStudentId
                    ? 'Saving...'
                    : 'Adding...';


            const payload =
            {
                nim:
                    nimInput.value.trim(),

                name:
                    nameInput.value.trim(),

                batch_id:
                    batchInput.value,

                status:
                    statusInput.value,

                cumulative_gpa:
                    gpaInput.value === ''
                        ? null
                        : gpaInput.value,
            };


            const url =
                editingStudentId
                    ? `/api/students/${editingStudentId}`
                    : '{{ route('api.students.store') }}';


            const response =
                await fetch(
                    url,
                    {
                        method:
                            editingStudentId
                                ? 'PUT'
                                : 'POST',

                        headers:
                        {
                            'Content-Type':
                                'application/json',

                            'Accept':
                                'application/json',

                            'X-CSRF-TOKEN':
                                document
                                    .querySelector(
                                        'meta[name="csrf-token"]'
                                    )
                                    .getAttribute('content'),
                        },

                        body:
                            JSON.stringify(payload),
                    }
                );


            if (response.ok)
            {
                window.location.reload();

                return;
            }


            let data = {};

            try
            {
                data =
                    await response.json();
            }
            catch (error)
            {
                // Ignore JSON parsing failure.
            }


            const messages =
                data.errors
                    ? Object.values(data.errors)
                        .flat()
                        .join('<br>')
                    : (
                        data.message
                        ||
                        'Unable to save student.'
                    );


            showFormErrors(messages);


            saveStudentButton.disabled =
                false;


            saveStudentButton.innerText =
                editingStudentId
                    ? 'Save Changes'
                    : 'Save Student';
        }


        /*
         * =========================================================
         * EDIT LIST TOGGLE
         * =========================================================
         */

        function toggleEditMode()
        {
            editMode =
                !editMode;


            document
                .querySelectorAll('.action-cell')
                .forEach(cell =>
                {
                    cell.style.display =
                        editMode
                            ? 'table-cell'
                            : 'none';
                });


            const actionHeader =
                document.querySelector(
                    'th:nth-child(6)'
                );


            if (actionHeader)
            {
                actionHeader.style.display =
                    editMode
                        ? 'table-cell'
                        : 'none';
            }
        }


        /*
         * Hide Edit buttons when page loads.
         */

        document.addEventListener(
            'DOMContentLoaded',
            () =>
            {
                document
                    .querySelectorAll('.action-cell')
                    .forEach(cell =>
                    {
                        cell.style.display =
                            'none';
                    });


                const actionHeader =
                    document.querySelector(
                        'th:nth-child(6)'
                    );


                if (actionHeader)
                {
                    actionHeader.style.display =
                        'none';
                }
            }
        );

    </script>

@endsection