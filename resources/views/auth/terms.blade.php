<x-guest-layout>
    <div class="w-full sm:max-w-3xl mx-auto bg-white rounded-lg shadow-md overflow-hidden p-6">
        <h1 class="text-2xl font-bold mb-4">Terms of Use</h1>
        <p class="text-sm text-gray-600 mb-6">
            <em>Last updated: {{ now()->format('Y M d') }}</em>
        </p>

        <p class="mb-6">
            Welcome to EventBooking (“we,” “our,” or “us”). By creating an account, booking an event, or using
            our services, you agree to these Terms of Use. Please read them carefully.
        </p>

        <h2 class="text-xl font-semibold mt-6 mb-2">1. Eligibility</h2>
        <ul class="list-disc ml-6 mb-6">
            <li>You must be at least 18 years old to register for an account.</li>
            <li>If registering on behalf of an organization, you confirm you have authority to bind it.</li>
        </ul>

        <h2 class="text-xl font-semibold mt-6 mb-2">2. User Accounts</h2>
        <ul class="list-disc ml-6 mb-6">
            <li>You are responsible for keeping your login credentials secure.</li>
            <li>All information you provide must be accurate and up to date.</li>
            <li>You may not impersonate others or share your account.</li>
        </ul>

        <h2 class="text-xl font-semibold mt-6 mb-2">3. Event Bookings</h2>
        <ul class="list-disc ml-6 mb-6">
            <li>Bookings are subject to event capacity and availability.</li>
            <li>Payments, cancellations, and refunds are governed by the event organizer’s policies.</li>
            <li>You may not book multiple times for the same event unless explicitly allowed.</li>
        </ul>

        <h2 class="text-xl font-semibold mt-6 mb-2">4. Waitlist</h2>
        <ul class="list-disc ml-6 mb-6">
            <li>If an event is full, you may join the waitlist.</li>
            <li>Waitlist placement does not guarantee entry; availability depends on cancellations or organizer action.</li>
        </ul>

        <h2 class="text-xl font-semibold mt-6 mb-2">5. Prohibited Conduct</h2>
        <ul class="list-disc ml-6 mb-6">
            <li>Do not use the platform for unlawful activities.</li>
            <li>Do not interfere with system functionality, servers, or networks.</li>
            <li>Do not misrepresent your identity or impersonate others.</li>
        </ul>

        <h2 class="text-xl font-semibold mt-6 mb-2">6. Intellectual Property</h2>
        <p class="mb-6">
            All content, branding, and code are owned by EventBooking or its licensors. You may not copy,
            distribute, or modify them without permission.
        </p>

        <h2 class="text-xl font-semibold mt-6 mb-2">7. Limitation of Liability</h2>
        <p class="mb-6">
            We are not responsible for event details provided by organizers, or for any damages, losses, or claims
            arising from your use of the Services. You use EventBooking at your own risk.
        </p>

        <h2 class="text-xl font-semibold mt-6 mb-2">8. Termination</h2>
        <p class="mb-6">
            We may suspend or terminate your account if you violate these Terms or misuse the Services.
        </p>

        <h2 class="text-xl font-semibold mt-6 mb-2">9. Changes to These Terms</h2>
        <p class="mb-6">
            We may update these Terms from time to time. Updates will be posted here with a new “Last updated” date.
        </p>

        <h2 class="text-xl font-semibold mt-6 mb-2">10. Contact Us</h2>
        <p>
            📧 <a href="mailto:support@example.com" class="text-blue-600 underline">support@example.com</a>
        </p>
    </div>
</x-guest-layout>
