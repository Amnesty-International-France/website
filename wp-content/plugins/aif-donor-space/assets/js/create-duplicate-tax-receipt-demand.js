const createDuplicateTaxReceiptDemand = async (taxReceiptReference) => {

    console.log(my_ajax_obj)

    if (!taxReceiptReference) {

        throw new Error("Taxt Receipt Reference not provided");
    }

    try {

        fetch(my_ajax_obj.ajax_url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                _ajax_nonce: my_ajax_obj.nonce, // nonce
                action: "my_tag_count", // action
                title: "my title" // data
            }),
        }).then((result) => {

            console.log(result);
        }).catch((e) => { console.log(e) })


    } catch (error) {

        console.log(err)

    }


}



