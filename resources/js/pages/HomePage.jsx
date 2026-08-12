import Navbar from '../components/home/Navbar';
import Hero from '../components/home/Hero';
import BookingBar from '../components/home/BookingBar';
import ValueProps from '../components/home/ValueProps';
import PopularDishes from '../components/home/PopularDishes';
import OurStory from '../components/home/OurStory';
import Testimonials from '../components/home/Testimonials';
import Instagram from '../components/home/Instagram';
import Newsletter from '../components/home/Newsletter';
import Footer from '../components/home/Footer';

export default function HomePage() {
    return (
        <div className="min-h-screen bg-white">
            <Navbar />
            <main>
                <Hero />
                <div className="bg-cream pb-8 lg:pb-10">
                    <BookingBar />
                    <ValueProps />
                </div>
                <PopularDishes />
                <OurStory />
                <Testimonials />
                <Instagram />
                <Newsletter />
            </main>
            <Footer />
        </div>
    );
}
