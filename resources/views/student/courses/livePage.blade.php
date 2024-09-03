@extends('layouts.student')
@section('content')
    {{-- Live Session --}}
    <div class="container">
        <div class="w-full min-h-[calc(100vh-6rem)] bg-gradient-to-tr from-gray-400 to-slate-100">
            <div class="max-w-screen-xl mx-auto  p-4 space-y-8 flex flex-col justify-start items-center">
                <div id="meeting-container"></div>
            </div>
        </div>


        <script src="https://source.zoom.us/3.8.0/lib/vendor/react.min.js"></script>
        <script src="https://source.zoom.us/3.8.0/lib/vendor/react-dom.min.js"></script>
        <script src="https://source.zoom.us/3.8.0/lib/vendor/redux.min.js"></script>
        <script src="https://source.zoom.us/3.8.0/lib/vendor/redux-thunk.min.js"></script>
        <script src="https://source.zoom.us/3.8.0/lib/vendor/lodash.min.js"></script>
        <script src="https://source.zoom.us/3.8.0/zoom-meeting-embedded-3.8.0.min.js"></script>

        <script>
            const client = ZoomMtgEmbedded.createClient()
            let meetingSDKElement = document.getElementById('meeting-container')
            client.init({
                debug: true,
                zoomAppRoot: meetingSDKElement,
                language: 'en-US',
                customize: {
                    meetingInfo: ['topic', 'host', 'mn', 'pwd', 'telPwd', 'invite', 'participant', 'dc', 'enctype'],
                    toolbar: {
                        buttons: [{
                            text: 'Custom Button',
                            className: 'CustomButton',
                            onClick: () => {
                                console.log('custom button')
                            }
                        }]
                    }
                }
            })

            client.join({
                sdkKey: '{{ config('services.zoom.client_id') }}',
                signature: '{{ $signature }}',
                meetingNumber: '{{ $meetingData['meetingNumber'] }}',
                password: '{{ $meetingData['password'] }}',
                userName: '{{ $meetingData['userName'] }}',
                userEmail: '{{ $meetingData['userEmail'] }}',
                role: 0 // 0 for attendee, 1 for host
            }).then(() => {
                console.log('Joined the meeting successfully');
            }).catch((error) => {
                console.error('Failed to join the meeting:', error);
            });
        </script>
    </div>
@endsection()
