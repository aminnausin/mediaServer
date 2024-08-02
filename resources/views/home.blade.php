<x-app-layout>
    @auth
    <script>var user = true;</script>
    @else
    <script>var user = false;</script>
    @endauth
</x-app-layout>