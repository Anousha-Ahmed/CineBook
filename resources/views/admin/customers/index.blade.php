<x-layouts.admin heading="Customers" subheading="{{ $users->total() }} accounts registered">
    <div class="space-y-5">
        <form method="GET" action="{{ route('admin.customers.index') }}" class="grid gap-3 rounded-2xl border border-white/[.07] bg-night-900/60 p-4 sm:grid-cols-[1fr_0.4fr_auto]">
            <input type="search" name="search" value="{{ request('search') }}" class="input" placeholder="Name or email">
            <select name="role" class="input">
                <option value="">All roles</option>
                @foreach (['customer', 'staff', 'admin'] as $role)
                    <option value="{{ $role }}" @selected(request('role') === $role)>{{ ucfirst($role) }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-ghost">Filter</button>
        </form>

        <div class="table-shell">
            @if ($users->isEmpty())
                <div class="px-6 py-16 text-center">
                    <p class="font-display text-lg font-semibold text-white">No accounts found</p>
                    <p class="mt-1 text-sm text-slate-500">Try a different search.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-white/[.02] text-[11px] uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="px-5 py-3.5 font-medium">Customer</th>
                                <th class="px-5 py-3.5 font-medium">Bookings</th>
                                <th class="px-5 py-3.5 font-medium">Verified</th>
                                <th class="px-5 py-3.5 font-medium">Role</th>
                                <th class="px-5 py-3.5 font-medium">Joined</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/[.05]">
                            @foreach ($users as $user)
                                <tr class="transition hover:bg-white/[.02]">
                                    <td class="px-5 py-3.5">
                                        <p class="font-medium text-slate-200">{{ $user->name }}</p>
                                        <p class="text-xs text-slate-500">{{ $user->email }}</p>
                                    </td>
                                    <td class="px-5 py-3.5 text-slate-400">
                                        <a href="{{ route('admin.bookings.index', ['customer' => $user->email]) }}" class="text-brand-400 hover:text-brand-300">{{ $user->bookings_count }}</a>
                                    </td>
                                    <td class="px-5 py-3.5">
                                        <span class="badge {{ $user->email_verified_at ? 'badge-green' : 'badge-slate' }}">
                                            {{ $user->email_verified_at ? 'Verified' : 'Pending' }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3.5">
                                        <form method="POST" action="{{ route('admin.customers.update', $user) }}" class="flex items-center gap-2">
                                            @csrf
                                            @method('PATCH')
                                            <select name="role" class="input h-9 w-32">
                                                @foreach (['customer', 'staff', 'admin'] as $role)
                                                    <option value="{{ $role }}" @selected($user->role === $role)>{{ ucfirst($role) }}</option>
                                                @endforeach
                                            </select>
                                            <button type="submit" class="text-xs font-medium text-brand-400 hover:text-brand-300">Apply</button>
                                        </form>
                                    </td>
                                    <td class="px-5 py-3.5 text-xs text-slate-500">{{ $user->created_at->format('j M Y') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-white/[.06] px-5 py-4">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.admin>
