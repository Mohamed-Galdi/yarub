<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title> {{ $certificate->user->name }} -شهادة إتمام و تميز</title>
    @vite(['resources/css/app.css'])
    <style type="text/css" media="print">
        @page {
            size: landscape;
            margin: 0;
        }
    </style>
</head>

<body dir="rtl">
    <div class="w-screen h-screen bg-gray-100 flex justify-center items-center">
        <div style="background-image: url({{ asset('assets/images/certif-bg.png') }})"
            class="w-[842px] h-[595px] bg-no-repeat  bg-center bg-cover relative">
            <h1 class="absolute left-1/2 -translate-x-1/2 top-20 font-hacen text-5xl text-slate-600"> شهادة إتمام و تميز
            </h1>
            <h2 class="absolute left-1/2 -translate-x-1/2 top-[11rem] font-judur text-xl">تمنح خصيصا و بإقتدار للطالب(ة)
            </h2>
            <h3 style="word-spacing: 2px"
                class="absolute left-1/2 -translate-x-1/2 top-52 font-arabic_handwrite text-6xl text-slate-800 ">
                {{ $certificate->user->name }}</h3>
            <div style="word-spacing: 2px"
                class="absolute w-5/6 px-4 left-1/2 -translate-x-1/2 top-72 font-arabic_handwrite text-xl text-slate-800 text-justify ">
                <p class="">تُمنح هذه الشهادة للطالبة/الطالب <span
                        class="text-2xl text-indigo-800">{{ $certificate->user->name }}</span>، تقديراً لجهوده/جهودها
                    المثمرة في إتمام دورة <span
                        class="text-2xl text-indigo-800">{{ $is_for_course ? $certificate->course->title : $certificate->lesson->title }}</span>
                    بنجاح وتفوق، والتي
                    تمَّت في <span
                        class="text-2xl text-indigo-800">{{ $certificate->created_at->format('d/m/Y') }}</span>، وقد
                    أظهر/أظهرت من خلال هذه الدورة مستوىً عالياً من الالتزام والتفاني، مما
                    يؤهله/يؤهلها لنيل هذه الشهادة بجدارة.</p>
            </div>
            <div class="flex justify-between items-center w-full px-24 absolute bottom-16 text-white">
                <div class="w-28 h-28 flex justify-center items-center">
                    <img src="{{ asset('assets/logos/logo_dark.png') }}" alt="">
                </div>
                <div class="w-32 h-28 flex justify-center items-center relative">
                    <img src="{{ asset('assets/images/signature.png') }}" alt="">
                </div>
                <div class="w-28 h-28 flex justify-center items-center relative">
                    <img src="{{ asset('assets/images/certife-year.png') }}" alt="">
                    <p
                        class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full text-center text-2xl  text-yellow-700 font-hacen">
                        {{ $certificate->created_at->format('Y') }}</p>
                </div>
            </div>
            <p style="word-spacing: 2px"
                class="absolute w-5/6 px-4 left-1/2 -translate-x-1/2 bottom-16 font-arabic_handwrite text-xl text-slate-800 text-center">
                هذه الشهادة مقدمة من طرف منصة يعرب لتعليم اللغة العربية</p>

        </div>

    </div>
</body>
<script>
    window.addEventListener('load', function() {
        // Function to print the page
        function printPage() {
            window.print(); // Print the page

            // Close the window after printing or cancelling
            setTimeout(function() {
                window.close(); // Close the window
            }, 1000); // Adjust the delay as needed (milliseconds)

            // After a short delay to allow the page to render, convert it to PDF
            setTimeout(function() {
                const pdf = new jsPDF(); // Create a new jsPDF instance
                pdf.addHTML(document.body, function() {
                    pdf.save('page_as_pdf.pdf'); // Save the PDF as 'page_as_pdf.pdf'
                });
            }, 2000); // Adjust the delay as needed (milliseconds)
        }

        printPage(); // Call the function to print the page and convert it to PDF
    });
</script>

</html>
