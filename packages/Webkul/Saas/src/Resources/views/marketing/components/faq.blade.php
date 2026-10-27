@props(['items'])

<div class="faq-list">
    @foreach ($items as $item)
        <details class="faq-item">
            <summary>{{ $item['question'] }}</summary>

            <div class="faq-answer">{{ $item['answer'] }}</div>
        </details>
    @endforeach
</div>
