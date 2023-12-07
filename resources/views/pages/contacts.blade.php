@extends('layouts.static')

@section('content')
<div class="static-page">
    <h1>Contact SkillSwap</h1>

    <p>Feel free to reach out to us! Whether you have questions, feedback, or just want to say hello, we're here for you.</p>

    <h2>Get in Touch with the Developers</h2>
    <div class="developers">
        <div class="developer">
            <img src="{{ url('assets/skillswap.png') }}" alt="SkillSwap Logo" />
            <h3>Luís Jesus</h3>
            <p><strong>Email:</strong> luis.jesus@skillswap.com</p>
            <p><strong>LinkedIn:</strong> <a href="https://www.linkedin.com/in/luis-jesus" target="_blank">Luís Jesus on LinkedIn</a></p>
            <p><strong>GitHub:</strong> <a href="https://github.com/luisjesus" target="_blank">Luís Jesus on GitHub</a></p>
            <p>Luís is passionate about technology and is dedicated to creating a vibrant community on SkillSwap. Connect with him to discuss ideas, report issues, or just chat about the exciting world of skills and collaboration!</p>
        </div>

        <div class="developer">
            <img src="{{ url('assets/skillswap.png') }}" alt="SkillSwap Logo" />
            <h3>Miguel Pedrosa</h3>
            <p><strong>Email:</strong> miguel.pedrosa@skillswap.com</p>
            <p><strong>LinkedIn:</strong> <a href="https://www.linkedin.com/in/miguel-pedrosa" target="_blank">Miguel Pedrosa on LinkedIn</a></p>
            <p><strong>GitHub:</strong> <a href="https://github.com/miguelpedrosa" target="_blank">Miguel Pedrosa on GitHub</a></p>
            <p>Miguel is an enthusiastic developer with a passion for building innovative solutions. Reach out to him for technical inquiries, collaboration opportunities, or to discuss the latest trends in the tech industry!</p>
        </div>

        <div class="developer">
            <img src="{{ url('assets/skillswap.png') }}" alt="SkillSwap Logo" />
            <h3>Miguel Rocha</h3>
            <p><strong>Email:</strong> miguel.rocha@skillswap.com</p>
            <p><strong>LinkedIn:</strong> <a href="https://www.linkedin.com/in/miguel-rocha" target="_blank">Miguel Rocha on LinkedIn</a></p>
            <p><strong>GitHub:</strong> <a href="https://github.com/miguelrocha" target="_blank">Miguel Rocha on GitHub</a></p>
            <p>Miguel is a creative mind with a keen interest in design and user experience. Contact him for design-related inquiries, feedback on the user interface, or to discuss the visual aspects of SkillSwap!</p>
        </div>
    </div>

    <p>If you have general inquiries or feedback, you can also reach us at <strong>info@skillswap.com</strong>.</p>

    <p>We appreciate your interest in SkillSwap and look forward to hearing from you!</p>
</div>
@endsection