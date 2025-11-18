@extends('layouts.web')

@section('content')
    <section class="bg-white">
        <div class="grid max-w-screen-xl px-4 pt-10 pb-8 mx-auto lg:gap-8 xl:gap-0 lg:py-16 lg:grid-cols-1 lg:pt-16">
            <h1 class="mb-8 text-4xl font-extrabold leading-none tracking-tight md:text-5xl xl:text-5xl">
                Užitečné kontakty a tipy pro vás
            </h1>

            <p class="text-xl">
                Hra je u konce – jste skvělí, že procvičujete vaše dovednosti.<br/>
                Abyste zůstali v bezpečí i do budoucna, máme pro vás pár ověřených kontaktů, kam se obrátit, když byste potřebovali radu nebo podporu.
            </p>

            <div class="mt-5">
                <h2 class="mb-3 text-2xl font-extrabold leading-none tracking-tight">
                    Pokračujte ve vzdělávání s Univerzitami třetího věku tzv. U3V:
                </h2>
                <p>Kurzy a přednášky pro seniory – nové znalosti, noví lidé, aktivní mysl.</p>
                <p><a href="https://au3v.cz/najdete-u3v-v-okoli-seznam-clenu" target="_blank" class="underline text-blue-600 hover:text-blue-800 visited:text-purple-600">Klikněte a najděte Univerzity třetího věku ve vašem okolí.</a></p>
            </div>

            <div class="mt-10">
                <h2 class="mb-3 text-2xl font-extrabold leading-none tracking-tight">
                    Potřebujete právní radu?
                </h2>
                <p>Nevíte si rady po podvodu nebo s problémem u nákupu? Zavolejte do právní poradny dTest – poradí Vám, jak postupovat (cena běžného hovoru).</p>
                <p><a href="https://www.dtest.cz/clanek-1530/spotrebitelsky-problem-volejte-nasi-poradnu?utm_source=GoG&utm_medium=poradna_pro_spotrebitele&utm_campaign=STR_S_Poradenstvi&gad_source=1&gclid=CjwKCAiAnKi8BhB0EiwA58DA4eY2cjyWCHwXo85G4GH8wCiWH2_IjuyUttPMrYsNMxzTBoKg0Ba-dB" target="_blank" class="underline text-blue-600 hover:text-blue-800 visited:text-purple-600">Klikněte a zjistěte více o právní poradně dTest.</a></p>
            </div>

            <div class="mt-10">
                <h2 class="mb-3 text-2xl font-extrabold leading-none tracking-tight">
                    Nejste na to sami
                </h2>
                <p>Pokud se cítíte pod tlakem nebo osaměle, zavolejte na Linku seniorů: <span class="font-bold">800 200 007</span> – zdarma a anonymně, denně 8:00-20:00.</p>
                <p><a href="https://linka-senioru.elpida.cz/" target="_blank" class="underline text-blue-600 hover:text-blue-800 visited:text-purple-600">Klikněte a zjistěte informací o lince seniorů.</a></p>
            </div>
        </div>
    </section>
@endsection
