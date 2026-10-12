<div class="space-y-card">
    <x-ui.card title="Academic Info" icon="work">
        <x-ui.detail-list>
            <x-ui.detail label="Lecturer Code">{{ $lecturer->lecturer_code }}</x-ui.detail>
            <x-ui.detail label="Key Performance Index">{{ $kpi !== null ? number_format($kpi, 2) : '-' }}</x-ui.detail>
            <x-ui.detail label="Academic Functional Rank">{{ $lecturer->jja }}</x-ui.detail>
            <x-ui.detail label="Latest Education">{{ $lecturer->latest_education }}</x-ui.detail>
            <x-ui.detail label="Status">{{ $lecturer->status }}</x-ui.detail>
            <x-ui.detail label="NIP">{{ $lecturer->nip }}</x-ui.detail>
        </x-ui.detail-list>
    </x-ui.card>

    <x-ui.card title="Personal Information" icon="person">
        <x-ui.detail-list>
            <x-ui.detail label="Full Name">{{ $lecturer->name }}</x-ui.detail>
            <x-ui.detail label="Gender">{{ $lecturer->gender ?? '-' }}</x-ui.detail>
            <x-ui.detail label="Email 1">{{ $lecturer->email_binus_edu }}</x-ui.detail>
            <x-ui.detail label="Email 2">{{ $lecturer->email_binus_ac_id }}</x-ui.detail>
            <x-ui.detail label="Phone Number">{{ $lecturer->phone_number }}</x-ui.detail>
        </x-ui.detail-list>
    </x-ui.card>
</div>
