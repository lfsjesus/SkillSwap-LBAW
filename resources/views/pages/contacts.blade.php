@extends('layouts.static')

@section('content')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="/">Home</a></li>
      <li class="breadcrumb-item active" aria-current="page">Contact Us</li>
    </ol>
</nav>

<div class="static-page">
    <h1>Contact SkillSwap</h1>

    <p>Feel free to reach out to us! Whether you have questions, feedback, or just want to say hello, we're here for you.</p>

    <h2>Get in Touch with the Developers</h2>
    <div class="developers">
        <div class="developer">
            <img src="{{ url('assets/luisj.jpg') }}" alt="SkillSwap Logo" />
            <h3>Luís Jesus</h3>
            <p><strong>Email:</strong> luis.jesus@skillswap.com</p>
            <p><strong>LinkedIn:</strong> <a href="https://www.linkedin.com/in/luis-jesus" target="_blank">Luís Jesus on LinkedIn</a></p>
            <p><strong>GitHub:</strong> <a href="https://github.com/luisjesus" target="_blank">Luís Jesus on GitHub</a></p>
        </div>

        <div class="developer">
            <img src="{{ url('assets/miguelp.jpg') }}" alt="SkillSwap Logo" />
            <h3>Miguel Pedrosa</h3>
            <p><strong>Email:</strong> miguel.pedrosa@skillswap.com</p>
            <p><strong>LinkedIn:</strong> <a href="https://www.linkedin.com/in/miguel-pedrosa" target="_blank">Miguel Pedrosa on LinkedIn</a></p>
            <p><strong>GitHub:</strong> <a href="https://github.com/migueljcpedrosa" target="_blank">Miguel Pedrosa on GitHub</a></p>
         </div>

        <div class="developer">
            <img src="{{ url('assets/miguelr.jpg') }}" alt="SkillSwap Logo" />
            <h3>Miguel Rocha</h3>
            <p><strong>Email:</strong> miguel.rocha@skillswap.com</p>
            <p><strong>LinkedIn:</strong> <a href="https://www.linkedin.com/in/miguel-rocha" target="_blank">Miguel Rocha on LinkedIn</a></p>
            <p><strong>GitHub:</strong> <a href="https://github.com/miguelrocha" target="_blank">Miguel Rocha on GitHub</a></p>
       </div>
    </div>

    <p>If you have general inquiries or feedback, you can also reach us at <strong>info@skillswap.com</strong>.</p>

    <p>We appreciate your interest in SkillSwap and look forward to hearing from you!</p>
</div>
@endsection
