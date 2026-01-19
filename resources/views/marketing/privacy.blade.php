<x-marketing-layout>
    <x-slot name="title">Privacy Policy - Event Schedule</x-slot>
    <x-slot name="description">Privacy Policy for Event Schedule - how we collect, use, and protect your data.</x-slot>

    <!-- Header -->
    <section class="py-16 bg-gray-50 dark:bg-gray-800/50">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-4">Privacy Policy</h1>
            <p class="text-gray-600 dark:text-gray-400">Last updated: {{ date('F j, Y') }}</p>
        </div>
    </section>

    <!-- Content -->
    <section class="py-16">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="prose prose-lg dark:prose-invert max-w-none">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mt-8 mb-4">1. Information We Collect</h2>
                <p class="text-gray-600 dark:text-gray-300 mb-4">
                    We collect information you provide directly to us when you:
                </p>
                <ul class="list-disc pl-6 text-gray-600 dark:text-gray-300 mb-6 space-y-2">
                    <li>Create an account (email address, name)</li>
                    <li>Create events (event details, descriptions, images)</li>
                    <li>Purchase tickets (name, email, payment information via Stripe)</li>
                    <li>Contact us for support</li>
                </ul>

                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mt-8 mb-4">2. How We Use Your Information</h2>
                <p class="text-gray-600 dark:text-gray-300 mb-4">
                    We use the information we collect to:
                </p>
                <ul class="list-disc pl-6 text-gray-600 dark:text-gray-300 mb-6 space-y-2">
                    <li>Provide, maintain, and improve our services</li>
                    <li>Process transactions and send related information</li>
                    <li>Send notifications about events you follow</li>
                    <li>Respond to your comments, questions, and support requests</li>
                    <li>Monitor and analyze trends, usage, and activities</li>
                </ul>

                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mt-8 mb-4">3. Information Sharing</h2>
                <p class="text-gray-600 dark:text-gray-300 mb-4">
                    We do not sell, trade, or otherwise transfer your personal information to third parties except:
                </p>
                <ul class="list-disc pl-6 text-gray-600 dark:text-gray-300 mb-6 space-y-2">
                    <li>To service providers who assist in our operations (e.g., Stripe for payments)</li>
                    <li>When required by law or to protect our rights</li>
                    <li>With your consent</li>
                </ul>

                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mt-8 mb-4">4. Third-Party Services</h2>
                <p class="text-gray-600 dark:text-gray-300 mb-6">
                    We use the following third-party services that may collect information:
                </p>
                <ul class="list-disc pl-6 text-gray-600 dark:text-gray-300 mb-6 space-y-2">
                    <li><strong>Stripe</strong> - For payment processing. Stripe's privacy policy applies to payment information.</li>
                    <li><strong>Google Analytics</strong> - For understanding how users interact with our service.</li>
                    <li><strong>Google Maps</strong> - For displaying venue locations.</li>
                </ul>

                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mt-8 mb-4">5. Data Security</h2>
                <p class="text-gray-600 dark:text-gray-300 mb-6">
                    We implement appropriate security measures to protect your personal information. However, no method of transmission over the Internet is 100% secure. We strive to use commercially acceptable means to protect your data but cannot guarantee its absolute security.
                </p>

                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mt-8 mb-4">6. Cookies</h2>
                <p class="text-gray-600 dark:text-gray-300 mb-6">
                    We use cookies and similar tracking technologies to track activity on our service and hold certain information. Cookies are files with a small amount of data which may include an anonymous unique identifier. You can instruct your browser to refuse all cookies or to indicate when a cookie is being sent.
                </p>

                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mt-8 mb-4">7. Your Rights</h2>
                <p class="text-gray-600 dark:text-gray-300 mb-4">
                    You have the right to:
                </p>
                <ul class="list-disc pl-6 text-gray-600 dark:text-gray-300 mb-6 space-y-2">
                    <li>Access your personal data</li>
                    <li>Correct inaccurate data</li>
                    <li>Request deletion of your data</li>
                    <li>Export your data</li>
                    <li>Unsubscribe from marketing communications</li>
                </ul>

                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mt-8 mb-4">8. Data Retention</h2>
                <p class="text-gray-600 dark:text-gray-300 mb-6">
                    We retain your personal information for as long as your account is active or as needed to provide you services. You can request deletion of your account and associated data at any time through your account settings.
                </p>

                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mt-8 mb-4">9. Children's Privacy</h2>
                <p class="text-gray-600 dark:text-gray-300 mb-6">
                    Our service is not directed to children under 13. We do not knowingly collect personal information from children under 13. If you are a parent or guardian and believe your child has provided us with personal information, please contact us.
                </p>

                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mt-8 mb-4">10. Changes to This Policy</h2>
                <p class="text-gray-600 dark:text-gray-300 mb-6">
                    We may update this privacy policy from time to time. We will notify you of any changes by posting the new privacy policy on this page and updating the "Last updated" date.
                </p>

                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mt-8 mb-4">11. Contact Us</h2>
                <p class="text-gray-600 dark:text-gray-300 mb-6">
                    If you have any questions about this Privacy Policy, please contact us at privacy@eventschedule.com.
                </p>
            </div>
        </div>
    </section>
</x-marketing-layout>
