

const disbaleAllButtons = (buttons) => {

    buttons.forEach(element => {
        element.disabled = true
    });
}

const enableAllButtons = (buttons) => {
    buttons.forEach(element => {
        element.disabled = false
    });
}


const createDuplicateTaxReceiptDemand = async (taxReceiptReference) => {
    const buttons = document.querySelectorAll('[data-id="get-duplicate-tax-receipt-button"]')
    const successMessage = document.getElementById(`aif-success-message-${taxReceiptReference}`);
    const errorMessage = document.getElementById(`aif-error-message-${taxReceiptReference}`);

    if (!buttons.length === 0) {
        return;
    }

    disbaleAllButtons(buttons);
    try {

        successMessage.classList.add("aif-hide")
        errorMessage.classList.add("aif-hide")
        const response = await fetch(aifDonorSpace.root + 'aif-donor-space/v1/duplicate-tax-receipt-request/', {
            method: 'POST',
            headers: {
                'X-WP-NONCE': aifDonorSpace.nonce,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                taxReceiptReference
            })
        });

        if (!response.ok) {
            throw new Error(`Request failed with status ${response.status}`);
        }
        successMessage.classList.remove("aif-hide");
    } catch (error) {
        errorMessage.classList.remove("aif-hide");
    } finally {
        enableAllButtons(buttons);
    }
}
