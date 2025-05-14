document.addEventListener('DOMContentLoaded', () => {
    const formInputs = document.querySelectorAll('input, select');

    formInputs.forEach(input => {
        // Automatically focus the next field on Enter
        input.addEventListener('keydown', (event) => {
            if (event.key === 'Enter') {
                event.preventDefault();
                const nextInput = getNextInput(input);
                if (nextInput) nextInput.focus();
            }
        });

        // Change field color when filled
        input.addEventListener('input', () => {
            if (input.value.trim()) {
                input.classList.add('filled');
            } else {
                input.classList.remove('filled');
            }
        });
    });

    function getNextInput(currentInput) {
        const inputs = Array.from(formInputs);
        const currentIndex = inputs.indexOf(currentInput);
        return inputs[currentIndex + 1] || null;
    }




});
