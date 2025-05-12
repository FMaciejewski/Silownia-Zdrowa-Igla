    const params = new URLSearchParams(window.location.search);
    const rodzaj = params.get("rodzaj");
    const miasto = params.get("miasto");
    const okres = params.get("okres");
    const dataRozpoczecia = params.get("data_rozpoczecia");

    document.getElementById("rodzaj").value = rodzaj;
    document.getElementById("miasto").value = miasto;
    document.getElementById("okres").value = okres;
    document.getElementById("data_rozpoczecia").value = dataRozpoczecia;