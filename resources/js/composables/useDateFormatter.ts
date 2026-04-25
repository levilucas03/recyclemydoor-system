export function useDateFormatter() {

    function formatPretty(dateString: string) {
        if (!dateString) return ''

        const date = new Date(dateString)

        return date.toLocaleDateString('en-GB', {
            day: 'numeric',
            month: 'short',
            year: '2-digit',
        })
    }

    return {
        formatPretty,
    }
}