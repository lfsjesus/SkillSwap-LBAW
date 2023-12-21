@extends('layouts.static')

@section('content')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="/">Home</a></li>
      <li class="breadcrumb-item active" aria-current="page">About Us</li>
    </ol>
</nav>

<div class="static-page">
    <h1>About Us</h1>
    <p>Welcome to SkillSwap, the thriving community where knowledge meets collaboration!</p>

    <p>At SkillSwap, we believe in the power of sharing knowledge and fostering a culture of collaboration. Our platform is designed for individuals who are passionate about their skills and eager to exchange ideas, projects, and expertise with like-minded individuals.</p>

    <section class="feature">
        <h2>Our Mission</h2>
        <p>Our mission is to create a vibrant space where users can showcase their talents, learn from others, and collaborate on exciting projects. Whether you're a seasoned professional or just starting on your journey, SkillSwap is the place to connect, inspire, and grow together.</p>
    </section>

    <section class="feature">
        <h2>Key Features</h2>
            <ul>
                <li><strong>Knowledge Sharing:</strong> Share your expertise in areas you are passionate about. Whether it's programming, design, marketing, or any other skill, SkillSwap is the platform to inspire and educate.</li>
                <li><strong>Project Showcase:</strong> Display your projects and creations to the community. Get feedback, find collaborators, or simply inspire others with your innovative work.</li>
                <li><strong>Collaboration Hub:</strong> Connect with fellow enthusiasts, form teams, and collaborate on exciting ventures. SkillSwap is more than a network; it's a community that thrives on collective creativity.</li>
            </ul>
    </section>

    <section class="feature">
        <h2>Meet the Developers</h2>
        <div class = developers>
            <div class="developer">
                <img src="{{ url('assets/luisj.jpg') }}" alt="Luis Jesus Profile Pictures" />
                <h3><strong>Luís Jesus</strong></h3>
                <p>Luís is passionate about technology and is dedicated to creating a vibrant community on SkillSwap. Connect with him to discuss ideas, report issues, or just chat about the exciting world of skills and collaboration!</p>
            </div>

            <div class="developer">
                <img src="{{ url('assets/miguelp.jpg') }}" alt="Miguel Pedrosa Profile Picture" />
                <h3><strong>Miguel Pedrosa</strong></h3>
                <p>Miguel is an enthusiastic developer with a passion for building innovative solutions. Reach out to him for technical inquiries, collaboration opportunities, or to discuss the latest trends in the tech industry!</p>
            </div>

            <div class="developer">
                <img src="{{ url('assets/miguelr.jpg') }}" alt="Miguel Rocha Profile Picture" />
                <h3><strong>Miguel Rocha</strong></h3>
                <p>Miguel is a creative mind with a keen interest in design and user experience. Contact him for design-related inquiries, feedback on the user interface, or to discuss the visual aspects of SkillSwap!</p>
            </div>
        </div>
    </section>

    <section class="call-to-action">
        <h2>Join SkillSwap Today!</h2>
        <p>Ready to embark on a journey of knowledge exchange and collaboration? Join SkillSwap today and become part of a dynamic community passionate about skills and innovation.</p>
    </section>

    <p>Thank you for being a part of SkillSwap!</p>
</div>
@endsection
