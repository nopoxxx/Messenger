import { BrowserRouter, Route, Routes } from 'react-router-dom'
import AuthPage from './components/AuthPage/AuthPage'
import ConfirmPage from './components/ConfirmPage/ConfirmPage'
import MainPage from './components/MainPage/MainPage'
import MessengerPage from './components/MessengerPage/MessengerPage'
import NotFoundPage from './components/NotFoundPage/NotFoundPage'

function App() {
	return (
		<BrowserRouter>
			<Routes>
				<Route path='*' element={<NotFoundPage />} />
				<Route path='/' element={<MainPage />} />
				<Route path='/auth' element={<AuthPage />} />
				<Route path='/confirm' element={<ConfirmPage />} />
				<Route path='/messenger' element={<MessengerPage />} />
			</Routes>
		</BrowserRouter>
	)
}

export default App
