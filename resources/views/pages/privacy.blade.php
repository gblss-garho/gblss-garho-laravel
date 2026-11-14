@extends('layouts.portal')

@section('title', 'Privacy')

@section('content')
    <h1>Privacy &amp; Data</h1>
    <p>Ye page batata hai ke {{ $info->name ?? 'GBLSS Garho' }} ki website par bachon aur staff ka kaun sa data rakha jata hai, kyun, aur kaun dekh sakta hai.</p>

    <h2>Kaun sa data rakha jata hai</h2>
    <div class="card">
        <div class="row"><b>Students:</b> naam, walid ka naam, GR number, class, tareekh paidaish, photo, hazri aur imtihan ke nateeje.</div>
        <div class="row"><b>Teachers / Staff:</b> naam, rabta ki tafseel, hazri, aur (sirf jab teacher razamandi de) chehre ki pehchan ka numerical data.</div>
        <div class="row"><b>Website visitors:</b> sirf login ke liye zaroori session cookie. Hum advertising ya tracking cookies istemal nahi karte.</div>
    </div>

    <h2>Data kyun istemal hota hai</h2>
    <div class="card">
        <div class="row">School ka record (General Register), hazri, nateeje, ID aur Admit cards, aur Leaving Certificate banane ke liye.</div>
        <div class="row">Data bechna, ishtihar ya kisi tijarati maqsad ke liye istemal nahi hota.</div>
    </div>

    <h2>Kaun dekh sakta hai</h2>
    <div class="card">
        <div class="row"><b>Public:</b> school ki maloomat, gallery, textbooks, staff ke naam aur toppers ki list. Kisi bachay ka nateeja ya ID card sirf us ke <b>GR number aur tareekh paidaish</b> dalne par milta hai.</div>
        <div class="row"><b>Teachers:</b> sirf apni classes ka data.</div>
        <div class="row"><b>Admin (Head Master):</b> poora school record.</div>
    </div>

    <h2>Teachers ka face data</h2>
    <div class="card">
        <div class="row">Face attendance ke liye chehre ki tasveer nahi, balkay chehre ke numbers (descriptor) mehfooz hote hain, aur sirf hazri tasdeeq ke liye istemal hote hain. Ye data teacher ki razamandi se liya jata hai.</div>
    </div>

    <h2>Hifazat</h2>
    <div class="card">
        <div class="row">Website HTTPS par chalti hai, har staff member ka alag login hai, galat password ki bar bar koshish par login kuch der ke liye band ho jata hai, aur database ka roz ka backup liya jata hai.</div>
    </div>

    <h2>Ghalti ya shikayat</h2>
    <div class="card">
        <div class="row">Agar kisi bachay ya staff ka data ghalat ho, ya aap kuch hatwana / theek karwana chahein, to school office mein Head Master se rabta karein. Theek karne ka kaam admin panel se kiya jata hai.</div>
    </div>
@endsection
