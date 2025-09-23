<x-guest-layout :containerClass="'w-1/2 mt-6 px-6 py-4 mx-auto bg-white rounded-lg shadow-md p-6'">
    <h1 class="text-xl font-semibold mb-2 text-center">Privacy Policy</h1>
        <p class="text-sm text-gray-600 mb-6 text-center border-b-2 border-gray-300 pb-2 ">
            <em>Last updated: {{ now()->format('Y M d') }}</em>
        </p>

    <p class="mb-6">
        This Privacy Policy explains how the Event Booking System (“we,” “our,” or “us”) collects, uses,
        stores, and protects your personal information when you use our services. By creating an account,
        you agree to this policy.
    </p>

    <h2 class="text-xl font-semibold mt-6 mb-2">1. Information We Collect</h2>
    <ul class="list-disc ml-6 mb-6">
        <li><strong>Name</strong> – to identify you in bookings and events.</li>
        <li><strong>Email address</strong> – for account login, communication, and notifications.</li>
        <li><strong>Password</strong> – stored in encrypted form for secure authentication.</li>
        <li><strong>Booking history</strong> – records of events you register for, attend, or cancel.</li>
    </ul>

    <h2 class="text-xl font-semibold mt-6 mb-2">2. Why We Collect This Information</h2>
    <ul class="list-disc ml-6 mb-6">
        <li><strong>Account authentication</strong> – to securely log you in and manage your account.</li>
        <li><strong>Event participation</strong> – to register, confirm, and manage event bookings.</li>
        <li><strong>Communication</strong> – to send confirmations, reminders, and updates.</li>
        <li><strong>System improvement</strong> – to analyze usage and enhance our services.</li>
    </ul>

    <h2 class="text-xl font-semibold mt-6 mb-2">3. How We Store and Protect Your Data</h2>
    <ul class="list-disc ml-6 mb-6">
        <li><strong>Passwords</strong> – stored in hashed and encrypted format.</li>
        <li><strong>Access control</strong> – only authorized personnel may access your data.</li>
        <li><strong>Data security</strong> – safeguards are in place to prevent loss, misuse, or alteration.</li>
    </ul>

    <h2 class="text-xl font-semibold mt-6 mb-2">4. Your Rights</h2>
    <p class="mb-6">
        You may view, update, or delete your personal information.  
        To exercise these rights, contact us at  
        <i class="bi bi-envelope"></i>
        <a href="mailto:support@example.com" class="text-blue-600 underline">support@example.com</a>.
    </p>

    <h2 class="text-xl font-semibold mt-6 mb-2">5. Data Sharing</h2>
    <p class="mb-6">
        We don’t sell or rent your data. We only share information if required by law or with your explicit consent.
    </p>

    <h2 class="text-xl font-semibold mt-6 mb-2">6. Changes to This Policy</h2>
    <p class="mb-6">
        We may update this policy from time to time. Updates will appear here with an updated “Last updated” date.
    </p>

    <h2 class="text-xl font-semibold mt-6 mb-2">7. Contact Us</h2>
    <p>
        <i class="bi bi-envelope"></i>
        <a href="mailto:support@example.com" class="text-blue-600 underline">support@example.com</a>
    </p>
</x-guest-layout>
