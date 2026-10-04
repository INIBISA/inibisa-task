<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center rounded-md border border-cyan-200 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-blue-800 shadow-sm transition duration-150 ease-in-out hover:bg-cyan-50 focus:outline-none focus:ring-2 focus:ring-cyan-300 focus:ring-offset-2 disabled:opacity-25']) }}>
    {{ $slot }}
</button>
