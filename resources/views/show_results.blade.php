<x-main-layout pageTitle="Countries & Capitals">
    <div class="container">

        <div class="text-center fs-3 mb-3">
            <p class="text-info">RESULTADOS FINAIS</p>
        </div>

        <div class="row fs-3">
            <div class="col text-end">Total de questões:</div>
            <div class="col text-info">{{ $totalQuestions }}</div>
        </div>
        <div class="row fs-3">
            <div class="col text-end">Respostas Certas:</div>
            <div class="col text-success">{{ $correctAnswers }}</div>
        </div>
        <div class="row fs-3">
            <div class="col text-end">Respostas Erradas:</div>
            <div class="col text-danger">{{ $wrongAnswers }}</div>
        </div>
        <div class="row fs-1">
            <div class="col text-end">Score Final:</div>
            <div class="col {{ $scoreFinal >= 50 ? 'text-success' : 'text-danger' }}">{{ $scoreFinal }}%</div>
        </div>

    </div>

    <!-- restart game -->
    <div class="text-center mt-5">
        <a href="{{ route('start_game') }}" class="btn btn-outline-primary mt-3 px-5">VOLTAR AO INÍCIO</a>
    </div>
</x-main-layout>
