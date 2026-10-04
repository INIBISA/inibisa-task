<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center rounded-md border border-transparent bg-blue-700 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-fuchsia-600 focus:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-cyan-300 focus:ring-offset-2 active:bg-blue-950']) }}>
    {{ $slot }}
</button>
