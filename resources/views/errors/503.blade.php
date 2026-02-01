<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>503 - Service Unavailable | {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="text-center px-4">
        <div class="mb-8">
            <h1 class="text-9xl font-bold text-purple-600">503</h1>
        </div>
        <h2 class="text-3xl font-semibold text-gray-800 mb-4">Service Unavailable</h2>
        <p class="text-gray-600 mb-8 max-w-md mx-auto">
            We're currently performing maintenance. Please check back shortly.
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <button onclick="location.reload()" class="inline-flex items-center justify-center px-6 py-3 bg-indigo-600 text-white font-medium rounded-lg hover:bg-indigo-700 transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Try Again
            </button>
        </div>
        <p class="text-gray-400 mt-8 text-sm">
            Expected downtime: 15-30 minutes
        </p>
    </div>
</body>
</html>
