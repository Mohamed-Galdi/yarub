@props(['url', 'params' => [], 'elementName'])

<button {{ $attributes->merge(['class' => 'delete-content']) }}
    data-url="{{ $url }}"
    data-params="{{ json_encode($params) }}"
    data-element-name="{{ $elementName }}">
    {{ $slot }}
</button>

<script>
    document.querySelectorAll('.delete-content').forEach(button => {
        button.addEventListener('click', function() {
            const url = this.dataset.url;
            const params = JSON.parse(this.dataset.params);
            const elementName = this.dataset.elementName;
            showDeleteConfirmation(url, params, elementName);
        });
    });

    function showDeleteConfirmation(url, params, elementName) {
        Swal.fire({
            title: `هل أنت متأكد من حذف هذا ${elementName}؟`,
            text: "لن تتمكن من التراجع عن هذا!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'نعم، قم بالحذف!',
            cancelButtonText: 'إلغاء'
        }).then((result) => {
            if (result.isConfirmed) {
                deleteContent(url, params, elementName);
            }
        });
    }

    function deleteContent(url, params, elementName) {
        axios.delete(url, {
            data: params,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(function(response) {
            if (response.data.success) {
                // Remove the element from the DOM
                const contentElement = document.querySelector(`[data-url="${url}"]`).closest('.content-form');
                if (contentElement) {
                    contentElement.remove();
                }
                
                Swal.fire(
                    'تم الحذف!',
                    `تم حذف ${elementName} بنجاح.`,
                    'success'
                );
            }
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