<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Replacement Appeal</title>
    <link rel="stylesheet" href="{{ asset('css/common/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components/sidebar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pages/appeal_form.css') }}">
</head>
<body>
    <x-header title="Student Portal" />

    <div class="app-container">
        <x-student_sidebar />

        <main class="main-content">
            <div class="appeal-form-container">
                <div class="form-header">
                    <h2>Submit Replacement Appeal</h2>
                    <p style="color: #7f8c8d;">Please review the class details and provide a valid reason for your replacement request.</p>
                </div>

                <div class="alert alert-warning">
                    <strong>⚠️ Important:</strong> Replacement appeals must be submitted with valid proof (medical certificate, police report, etc.). Appeals without proper documentation may be rejected.
                </div>

                <div class="class-info-section">
                    <!-- Original Class -->
                    <div class="class-card original">
                        <h3>📅 Original Class (Missed)</h3>
                        @if($originalClass)
                        <div class="class-detail">
                            <strong>Subject:</strong> {{ $originalClass->subject->subjectId }}
                        </div>
                        <div class="class-detail">
                            <strong>Name:</strong> {{ $originalClass->subject->subjectName }}
                        </div>
                        <div class="class-detail">
                            <strong>Date:</strong> {{ date('D, d M Y', strtotime($originalClass->classDate)) }}
                        </div>
                        <div class="class-detail">
                            <strong>Time:</strong> {{ date('h:i A', strtotime($originalClass->startTime)) }} - {{ date('h:i A', strtotime($originalClass->endTime)) }}
                        </div>
                        <div class="class-detail">
                            <strong>Venue:</strong> {{ $originalClass->venue->venueId }}
                        </div>
                        <div class="class-detail">
                            <strong>Group:</strong> {{ $originalClass->classGroup->classGroupId ?? 'N/A' }}
                        </div>
                        @endif
                    </div>

                    <!-- Replacement Class -->
                    <div class="class-card replacement">
                        <h3>✅ Replacement Class (Request)</h3>
                        <div class="class-detail">
                            <strong>Subject:</strong> {{ $replacementClass->subject->subjectId }}
                        </div>
                        <div class="class-detail">
                            <strong>Name:</strong> {{ $replacementClass->subject->subjectName }}
                        </div>
                        <div class="class-detail">
                            <strong>Date:</strong> {{ date('D, d M Y', strtotime($replacementClass->classDate)) }}
                        </div>
                        <div class="class-detail">
                            <strong>Time:</strong> {{ date('h:i A', strtotime($replacementClass->startTime)) }} - {{ date('h:i A', strtotime($replacementClass->endTime)) }}
                        </div>
                        <div class="class-detail">
                            <strong>Venue:</strong> {{ $replacementClass->venue->venueId }}
                        </div>
                        <div class="class-detail">
                            <strong>Group:</strong> {{ $replacementClass->classGroupId}}
                        </div>
                    </div>
                </div>

                <form action="{{ route('replacement.submit') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="original_class_id" value="{{ $originalClass->timetableId ?? '' }}">
                    <input type="hidden" name="requested_class_id" value="{{ $replacementClass->timetableId }}">

                    <div class="form-group">
                        <label for="reason">
                            Reason for Replacement <span class="required">*</span>
                        </label>
                        <textarea 
                            id="reason" 
                            name="reason" 
                            required 
                            placeholder="Please provide a detailed reason for your replacement request (minimum 10 characters)..."
                        >{{ old('reason') }}</textarea>
                        @error('reason')
                            <div style="color: #e74c3c; font-size: 12px; margin-top: 5px;">{{ $message }}</div>
                        @enderror
                        <div class="form-help-text">
                            Valid reasons: Medical emergency, family emergency, accident, official university events, etc.
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="proof">
                            Supporting Document (Proof)
                        </label>
                        <input 
                            type="file" 
                            id="proof" 
                            name="proof" 
                            accept=".pdf,.jpg,.jpeg,.png"
                        >
                        @error('proof')
                            <div style="color: #e74c3c; font-size: 12px; margin-top: 5px;">{{ $message }}</div>
                        @enderror
                        <div class="form-help-text">
                            Accepted formats: PDF, JPG, PNG (Max size: 5MB). Medical certificate, police report, official letter, etc.
                        </div>
                    </div>

                    <div class="button-group">
                        <a href="{{ route('replacement.myAppeals') }}" class="btn btn-cancel">Cancel</a>
                        <button type="submit" class="btn btn-submit">Submit Appeal</button>
                    </div>
                </form>
            </div>
            </div>
        </main>
    </div>

    <script src="{{ asset('js/sidebar.js') }}"></script>
</body>
</html>
