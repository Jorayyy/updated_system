<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Performance Review Details') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-6 flex justify-between items-center">
                        <div>
                            <h3 class="text-2xl font-bold">{{ $performanceReview->review_period }}</h3>
                            <p class="text-sm text-gray-500">Review Date: {{ $performanceReview->review_date->format('M d, Y') }}</p>
                        </div>
                        <div class="text-right">
                             <span class="px-4 py-2 inline-flex text-lg font-bold rounded-full 
                                {{ $performanceReview->rating >= 4 ? 'bg-green-100 text-green-800' : 
                                   ($performanceReview->rating >= 3 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                Rating: {{ $performanceReview->rating }}/5
                            </span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h4 class="font-semibold text-gray-700 mb-2">Employee</h4>
                            <p class="text-lg">{{ $performanceReview->employee->name }}</p>
                            <p class="text-sm text-gray-500">{{ $performanceReview->employee->email }}</p>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h4 class="font-semibold text-gray-700 mb-2">Reviewer</h4>
                            <p class="text-lg">{{ $performanceReview->reviewer->name ?? 'N/A' }}</p>
                            <p class="text-sm text-gray-500">{{ $performanceReview->reviewer->email ?? '' }}</p>
                        </div>
                    </div>

                    <div class="space-y-6">
                        @if($performanceReview->strengths)
                        <div>
                            <h4 class="font-semibold text-gray-800 text-lg border-b pb-2 mb-2">Strengths</h4>
                            <div class="prose max-w-none text-gray-600">
                                {!! nl2br(e($performanceReview->strengths)) !!}
                            </div>
                        </div>
                        @endif

                        @if($performanceReview->improvements)
                        <div>
                            <h4 class="font-semibold text-gray-800 text-lg border-b pb-2 mb-2">Areas for Improvement</h4>
                            <div class="prose max-w-none text-gray-600">
                                {!! nl2br(e($performanceReview->improvements)) !!}
                            </div>
                        </div>
                        @endif

                        @if($performanceReview->goals)
                        <div>
                            <h4 class="font-semibold text-gray-800 text-lg border-b pb-2 mb-2">Goals for Next Period</h4>
                            <div class="prose max-w-none text-gray-600">
                                {!! nl2br(e($performanceReview->goals)) !!}
                            </div>
                        </div>
                        @endif

                        @if($performanceReview->comments)
                        <div>
                            <h4 class="font-semibold text-gray-800 text-lg border-b pb-2 mb-2">Additional Comments</h4>
                            <div class="prose max-w-none text-gray-600">
                                {!! nl2br(e($performanceReview->comments)) !!}
                            </div>
                        </div>
                        @endif
                    </div>

                    <div class="mt-8 border-t pt-6 flex flex-col md:flex-row justify-between items-center gap-4">
                        <div class="text-sm text-gray-500 flex items-center gap-2">
                            Review Status: 
                            <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide
                                {{ $performanceReview->status === 'acknowledged' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                                {{ $performanceReview->status }}
                            </span>
                        </div>

                        <div class="flex gap-3">
                            @if(auth()->id() === $performanceReview->employee_id && $performanceReview->status !== 'acknowledged')
                            <form action="{{ route('performance-reviews.acknowledge', $performanceReview) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700 transition font-medium shadow-sm flex items-center gap-2" 
                                    onclick="return confirm('By acknowledging this review, you confirm that you have discussed it with your reviewer.');">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    Acknowledge Review
                                </button>
                            </form>
                            @endif
                            
                            <a href="{{ route('performance-reviews.index') }}" class="bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-50 transition font-medium shadow-sm">
                                Back to List
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
