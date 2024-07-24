@props(['url', 'params' => [], 'elementName'])

<button type="button" {{ $attributes->merge(['class' => 'delete-content']) }} data-url="{{ $url }}"
    data-params="{{ json_encode($params) }}" data-element-name="{{ $elementName }}">
    {{ $slot }}
</button>

<script>
    document.querySelectorAll('.delete-content').forEach(button => {
        button.addEventListener('click', function() {
            const url = this.dataset.url;
            const params = JSON.parse(this.dataset.params);
            const elementName = this.dataset.elementName;
            console.log(url, params, elementName);
            showDeleteConfirmation(url, params, elementName);
        });
    });

    function showDeleteConfirmation(url, params, elementName) {
        Swal.fire({
            title: `هل أنت متأكد من حذف هذا ${elementName}؟`,
            text: "لن تتمكن من التراجع عن هذا!",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#d33', // red
            cancelButtonColor: '#808080', //gray
            confirmButtonText: 'نعم، قم بالحذف!',
            cancelButtonText: 'إلغاء'
        }).then((result) => {
            if (result.isConfirmed) {
                deleteContent(url, params, elementName);
            }
        });
    }

    function deleteContent(url, params, elementName) {
        axios.post(url, {
                _method: 'DELETE'
            }, {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(function(response) {


                Swal.fire(
                    'تم الحذف!',
                    `تم حذف ${elementName} بنجاح.`,
                    'success'
                );
                // refresh the page
                window.location.reload();

            })
            .catch(function(error) {
                console.error('Delete error:', error);
                Swal.fire(
                    'خطأ!',
                    `حدث خطأ أثناء حذف ${elementName}. الرجاء المحاولة مرة أخرى.`,
                    'error'
                );
            });
    }
</script>
