<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Performance Reviews') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            @if(auth()->user()->hasRole('super_admin') && !empty($ratingStats))
            <!-- Admin Dashboard: Rating Distribution Graph -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium mb-4">Overall Performance Ratings Distribution</h3>
                    <div class="h-64 app-chart-container">
                        <canvas id="ratingsChart"></canvas>
                    </div>
                </div>
            </div>

            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const ctx = document.getElementById('ratingsChart');
                    if (ctx) {
                        new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: ['1 - Poor', '2 - Fair', '3 - Satisfactory', '4 - Very Good', '5 - Excellent'],
                                datasets: [{
                                    label: '# of Employees',
                                    data: [{{ implode(',', $ratingStats) }}],
                                    backgroundColor: [
                                        'rgba(239, 68, 68, 0.6)',  // Red for 1
                                        'rgba(249, 115, 22, 0.6)', // Orange for 2
                                        'rgba(234, 179, 8, 0.6)',  // Yellow for 3
                                        'rgba(59, 130, 246, 0.6)', // Blue for 4
                                        'rgba(34, 197, 94, 0.6)'   // Green for 5
                                    ],
                                    borderColor: [
                                        'rgb(239, 68, 68)',
                                        'rgb(249, 115, 22)',
                                        'rgb(234, 179, 8)',
                                        'rgb(59, 130, 246)',
                                        'rgb(34, 197, 94)'
                                    ],
                                    borderWidth: 1
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        ticks: {
                                            stepSize: 1
                                        }
                                    }
                                }
                            }
                        });
                    }
                });
            </script>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium">{{ auth()->user()->hasRole('super_admin') ? 'All Employee Reviews' : 'My Reviews' }}</h3>
                        @if(auth()->user()->isHr() || auth()->user()->isAdmin())
                        <a href="{{ route('performance-reviews.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            New Review
                        </a>
                        @endif
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Period</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Review Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rating</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($reviews as $review)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $review->review_period }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $review->review_date->format('M d, Y') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $review->rating >= 80 ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                            {{ $review->rating }}/100
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ ucfirst($review->status) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="{{ route('performance-reviews.show', $review) }}" class="text-indigo-600 hover:text-indigo-900">View</a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">No performance reviews found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $reviews->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
