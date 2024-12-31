const createDuplicateTaxReceiptDemand = async (taxReceiptReference) => {

    console.log(taxReceiptReference);

    fetch(aifDonorSpace.root + 'aif-donor-space/v1/duplicate-tax-receipt-request/', {
        method: 'POST',
        headers: {
            'X-WP-NONCE': aifDonorSpace.nonce,
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            taxReceiptReference
        })
    })
        .then(response => response.json())
        .then(data => {
            console.log(data);
        })
        .catch(error => {
            console.error('Error:', error);
        });


}



